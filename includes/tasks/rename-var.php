<?php
    /**
     * Lily - Task to find and rename variables
     * 
     * @author Matheus <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily\Tasks;

    class RenameVar extends \Lily\Task {
        const REQUIRED_PARAMS = ["var", "rename"];

        const NAME = "RenameVar";

        public $var = null;
        public $rename = null;

        public function __construct($params) {
            parent::__construct($params);

            $this->var = str_replace("$", "", $params->var);
            $this->rename = str_replace("$", "", $params->rename);
        }

        protected function run(\Lily\File $file) {
            // Add the node instruction for the variable rename
            $file->add_node_instruction([
                // Variable node
                "node" => "\PhpParser\Node\Expr\Variable",
                // When leaving the node
                "when" => "leave",
                // Checks if the name of the variable is the variable that needs to be replaced
                "if" => [
                    "name" => ["===", $this->var]
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