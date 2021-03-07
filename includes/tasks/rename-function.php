<?php
    /**
     * Lily - Task to find and rename variables
     * 
     * @author Matheus <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily\Tasks;

    class RenameFunction extends \Lily\Task {
        const REQUIRED_PARAMS = ["function", "rename"];

        const NAME = "RenameFunction";

        public $function = null;
        public $rename = null;

        public function __construct($params) {
            parent::__construct($params);

            $this->function = $params->function;
            $this->rename = $params->rename;
        }

        protected function run(\Lily\File $file) {
            // Add a new node visitor to the traverser
            /*$file->get_traverser()->addVisitor(new class($this) extends \PhpParser\NodeVisitorAbstract {
                public function __construct($task) {
                    $this->task = $task;
                }

                public function enterNode(\PhpParser\Node $node) {
                    // Check if node is a function
                    // and if the function name is the same as the function to be renamed
                    if (
                        $node instanceof \PhpParser\Node\Stmt\Function_ &&
                        $node->name->toString() === $this->task->function
                    ) {
                        // Set the new name
                        $node->name->name = $this->task->rename;
                    }
                }
            });*/

            // Add the node instruction for the function rename
            $file->add_node_instruction([
                // Function node
                "node" => "\PhpParser\Node\Stmt\Function_",
                // When entering the node
                "when" => "enter",
                // Checks if the name of the function is the function that needs to be replaced
                "if" => [
                    "name" => ["===", $this->function]
                ],
                // Do a "set" $node->name = $this->rename
                "do" => [
                    [
                        "action" => "set",
                        "vars" => [
                            "name" => $this->rename
                        ]
                    ]
                ]
            ]);

            return $file;
        }
    }