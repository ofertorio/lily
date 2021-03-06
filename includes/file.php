<?php
    /**
     * Lily - File parser class
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    class File {
        public $name;
        public $content;
        private $traverser;
        private $ast;
        private $node_finder;

        private $node_instructions = [];

        public function __construct($name = null, $content = null) {
            $this->name = $name;
            $this->content = $content;
        }

        /**
         * Adds a node traverser instruction
         *
         * @param \Lily\Task &$task A reference to the task that will run this instruction
         * @param array|File\Instruction $instruction The file instruction
         * @return void
         */
        public function add_node_instruction(\Lily\Task &$task, $instruction) {
            // Check if it's an array
            if (is_array($instruction)) {
                // Convert it to an instruction
                $instruction = new File\Instruction($instruction, $task);
            } else
            // Check if it's not an instance of File\Instruction
            if (!($instruction instanceof File\Instruction)) {
                throw new Error("Invalid node instruction", "INVALID_NODE_INSTRUCTION");
            }

            // Create the action array if doesn't exists yet
            $this->node_instructions[$instruction->get_when()] = $this->node_instructions[$instruction->get_when()] ?? [];

            // Append the instruction to it
            $this->node_instructions[$instruction->get_when()][] = $instruction;
        }

        /**
         * Retrieves all instructions by type
         *
         * @param string $type An array of instructions to be called
         * @return array[array]
         */
        public function get_instructions(string $type) {
            return $this->node_instructions[$type] ?? null;
        }

        /**
         * Applies all node instructions to the current traverser
         *
         * @return void
         */
        private function apply_node_instructions() {
            // Add a new node visitor to the traverser
            $this->get_traverser()->addVisitor(new class($this) extends \PhpParser\NodeVisitorAbstract {
                public function __construct($file) {
                    $this->file = $file;
                }

                /**
                 * Applies all instructions to a node for a given type
                 *
                 * @param string $type The instruction type to be applied
                 * @param \PhpParser\Node $node The node to be actioned
                 * @return \PhpParser\Node
                 */
                private function apply_instructions(string $type, \PhpParser\Node $node): \PhpParser\Node {
                    // Retrieve all instructions by type
                    $instructions = $this->file->get_instructions($type);

                    // Check if has no leave instructions
                    if (empty($instructions)) {
                        return $node;
                    }

                    // Iterate over all instructions
                    foreach($instructions as $instruction) {
                        // Get the node type
                        $instruction_node = $instruction->node;

                        // Check if it's not the same as the current node
                        if (!($node instanceof $instruction_node)) {
                            continue;
                        }

                        // Extract the parameters
                        $if = (array) $instruction->if;
                        $do = (array) $instruction->do;

                        // Check for all instructions
                        foreach($if as $index => $cnd) {
                            // Variable that will later handle if the condition was met
                            $condition = false;

                            // Check if condition is a callback
                            if (is_callable($cnd)) {
                                // Try matching it with the current node
                                $condition = call_user_func_array($cnd, [$node]);
                            } else {
                                // Retrieve the index value
                                $node_index = $node->{$index};

                                // Check if the condition value is a string, and node index has a toString() method
                                if (is_string($cnd[1]) && is_callable([$node_index, "toString"])) {
                                    // Call it
                                    $node_index = $node_index->toString();
                                }

                                // Evaluate the condition
                                eval("\$condition = \$node_index " . $cnd[0] . " " . escapeshellarg($cnd[1]) . ";");
                            }

                            // Check if the condition was not met
                            if (!$condition) {
                                // Assert the failed condition
                                $instruction->do_assertion(File\Instruction::ASSERT_FAILED_CONDITION_NOT_MET, null);

                                // Break the instruction
                                break 2;
                            }
                        }

                        // Do all instructions
                        foreach($do as $action) {
                            // Check if it's callable
                            if (is_callable($action)) {
                                // Just call the action
                                call_user_func_array($action, [$node]);
                                continue;
                            }

                            // Extract the action name
                            $action_name = $action["action"];
                            unset($action["action"]);

                            // Check if is settings variables
                            if ($action_name === "set") {
                                // Iterate over all variables
                                foreach($action["vars"] ?? $action["variables"] as $var => $value) {
                                    // Set the node variable value
                                    $node->{$var} = $value;
                                }
                            }
                        }
                    }

                    return $node;
                }

                public function leaveNode(\PhpParser\Node $node) {
                    return $this->apply_instructions("leave", $node);
                }

                public function enterNode(\PhpParser\Node $node) {
                    return $this->apply_instructions("enter", $node);
                }
            });
        }

        /**
         * Saves the file contents
         *
         * @param string $output The output file name
         * @return boolean
         */
        public function save(string $output) {
            // Process and get the modified content
            $content = $this->get_modified_content();

            \Lily\Console::log("saving", basename($this->name), "to", $output);
            return file_put_contents($output, $content);
        }

        /**
         * Loads and retrieves the file contents
         *
         * @return string
         */
        public function get_content() {
            // Check if no content is yet loaded
            if (empty($this->content)) {
                $this->content = @file_get_contents($this->name);
            }

            return $this->content;
        }

        /**
         * Sets the file contents
         *
         * @param string $content
         * @return void
         */
        public function set_content(string $content) {
            $this->content = $content;
        }

        /**
         * Returns the parsed and modified file content
         *
         * @return string
         */
        public function get_modified_content() {
            // First of all, get the contents
            $content = $this->get_content();

            $ast = $this->ast ?? null;

            // Check if has any traverser instruction
            if (!empty($this->node_instructions)) {
                // Prepare the traverser
                $this->apply_node_instructions();
            }

            // Check if any traverser was created
            if (!empty($this->traverser)) {
                // Let the traverser perform
                $ast = $this->traverser->traverse($this->get_ast());
            }

            // Check if the AST was created
            if (!empty($ast)) {
                // Convert it back to PHP
                $pretty_printer = new \PhpParser\PrettyPrinter\Standard();
                $content = $pretty_printer->printFormatPreserving($ast, $this->ast, $this->tokens);

                // Fix some buggy issues with the format preserving
                $content = preg_replace("/(function .+?)\n.+\{/m", '$1 {', $content);
            }

            return $content;
        }

        /**
         * Loads and retrieves the file AST
         *
         * @return array[\PhpParser\Node]
         */
        public function get_ast() {
            // Check if no AST is yet loaded
            if (empty($this->ast)) {
                try {
                    $lexer = new \PhpParser\Lexer\Emulative([
                        "usedAttributes" => [
                            "comments",
                            "startLine", "endLine",
                            "startTokenPos", "endTokenPos"
                        ]
                    ]);

                    // Create a new PHP parser
                    $parser = new \PhpParser\Parser\Php7($lexer);

                    // Try creating the AST parser
                    $this->ast = $parser->parse($this->get_content());

                    // Save the tokens
                    $this->tokens = $lexer->getTokens();
                } catch(\PhpParser\Error $e) {
                    \Lily\Console::error("An error ocurred while parsing an AST:", $e->getMessage());
                    return null;
                }
            }

            return $this->ast;
        }

        /**
         * Creates or retrieves a node traverser
         *
         * @return \PhpParser\NodeTraverser
         */
        public function get_traverser(): \PhpParser\NodeTraverser {
            if (empty($this->traverser)) {
                // Create the node traverser
                $this->traverser = new \PhpParser\NodeTraverser();
            }

            return $this->traverser;
        }

        /**
         * Creates or retrieves a node finder
         *
         * @return \PhpParser\NodeFinder
         */
        public function get_node_finder(): \PhpParser\NodeFinder {
            if (empty($this->node_finder)) {
                // Create the node node_finder
                $this->node_finder = new \PhpParser\NodeFinder();
            }

            return $this->node_finder;
        }
    }