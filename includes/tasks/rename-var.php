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
            // Add a new node visitor to the traverser
            $file->get_traverser()->addVisitor(new class($this) extends \PhpParser\NodeVisitorAbstract {
                public function __construct($task) {
                    $this->task = $task;
                }

                public function leaveNode(\PhpParser\Node $node) {
                    // Check if node is a variable
                    // and if the variable name is the same as the variable to be renamed
                    if (
                        $node instanceof \PhpParser\Node\Expr\Variable &&
                        $node->name === $this->task->var
                    ) {
                        // Set the new name
                        $node->name = $this->task->rename;

                        return $node;
                    }
                }
            });

            return $file;
        }
    }