<?php
    /**
     * Lily - Task to refactor functions
     * 
     * @author Matheus <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily\Tasks;

    class RefactorFunction extends \Lily\Task {
        const REQUIRED_PARAMS = ["function"];

        const NAME = "RefactorFunction";

        public $function = null;
        public $rename = null;

        public function __construct($params) {
            parent::__construct($params);

            // Save the function name
            $this->function = $params->function;
        }

        protected function run(\Lily\File $file) {
            // Add the node instruction for the function rename
            $file->add_node_instruction($this, [
                // Function node
                "node" => "\PhpParser\Node\Stmt\Function_",
                // When leaving the node
                "when" => "leave",
                // Checks if the name of the function is the function that needs to be replaced
                "if" => [
                    "name" => ["===", $this->function]
                ],
                // Do a "set" $node->stmts = $this->node->stmts
                "do" => [
                    [
                        "action" => "set",
                        "vars" => [
                            "stmts" => $this->node->stmts
                        ]
                    ]
                ]
            ]);

            return $file;
        }
    }