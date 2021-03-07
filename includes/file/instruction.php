<?php
    /**
     * Lily - File node instruction
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily\File;

    class Instruction {
        /**
         * Assertion constants
         * 
         * @var int
         */
        const ASSERT_FAILED_CONDITION_NOT_MET = -1;
        const ASSERT_FAILED_GENERAL = 0;
        const ASSERT_SUCCEEDED = 1;

        public $node;
        public $if;
        public $do;
        private $assert;
        private $when;

        /**
         * An array of valid options
         * 
         * @var array[string]
         */
        const VALID_OPTIONS = ["node", "if", "when", "do", "assert"];

        public function __construct(array $data) {
            // Check if has all the needed instructions
            if (empty($data["node"]) || empty($data["if"]) || empty($data["when"]) || empty($data["do"])) {
                throw new \Lily\Error("A traverser instruction needs to have a node, an if condition, when to do the action and a do action.", "INVALID_NODE_INSTRUCTION");
            }

            // Check if it's a valid node class
            if (!class_exists($data["node"])) {
                throw new \Lily\Error("Invalid node class name.", "INVALID_CLASS");
            }

            // Iterate over all data
            foreach($data as $key => $value) {
                // Check if it's a valid option
                if (in_array($key, self::VALID_OPTIONS)) {
                    // Set the value
                    $this->{$key} = $value;
                }
            }
        }
        
        /**
         * Returns when the instruction needs to be executed
         *
         * @return string
         */
        public function get_when() {
            return $this->when;
        }

        /**
         * Executes the assertion if any
         *
         * @param integer $reason
         * @param array $data
         * @return void
         */
        public function do_assertion(int $reason, array $data = []) {
            // Check if has any assertion to be executed
            if (empty($this->assert)) {
                return;
            }

            // Check if the assertion is a function
            if (is_callable($this->assert)) {
                // Call it
                return call_user_func_array($this->assert, [$reason, $data]);
            } else
            // Check if it's a boolean
            if (is_bool($this->assert)) {
                // Stop the execution
                throw new \Lily\Error("Assertion failed with code " . ((string) $reason) . ".", "ASSERTION_FAILED", $data);
            }

            return false;
        }
    }