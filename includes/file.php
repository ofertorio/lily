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

        public function __construct($name = null, $content = null) {
            $this->name = $name;
            $this->content = $content;
        }

        /**
         * Saves the file contents
         *
         * @param string $output The output file name
         * @return boolean
         */
        public function save(string $output) {
            // First of all, get the contents
            $content = $this->get_content();

            $ast = $this->ast ?? null;

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