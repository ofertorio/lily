<?php
    /**
     * Lily - Patch class
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    /*
     * T_ML_COMMENT does not exist in PHP 5.
     * The following three lines define it in order to
     * preserve backwards compatibility.
     *
     * The next two lines define the PHP 5 only T_DOC_COMMENT,
     * which we will mask as T_ML_COMMENT for PHP 4.
    */
    if (!defined("T_ML_COMMENT")) {
        define("T_ML_COMMENT", T_COMMENT);
    } else {
        define("T_DOC_COMMENT", T_ML_COMMENT);
    }

    class Patch {
        /**
         * A comment regex with all valid annotation names
         * 
         * @var RegExp
         */
        static $comment_regex;

        /**
         * The patch name
         *
         * @var string
         */
        public $name = "Unnamed";

        /**
         * A handler for all tasks that this patch does
         *
         * @var array[\Lily\Task]
         */
        private $tasks = [];

        /**
         * Adds a new task to be patched
         *
         * @param array|\Lily\Task $task
         * @return \Lily\Task
         */
        public function add_task($task) {
            // Check if it's a valid task
            if (!($task instanceof \Lily\Task)) {
                throw new \Error("Not a valid Lily patch task, Lily is sad. 😞");
            }

            return array_push($this->tasks, $task);
        }

        /**
         * Returns all patch tasks
         *
         * @return array[\Lily\Task]
         */
        public function get_tasks() {
            return $this->tasks;
        }

        /**
         * Parses a patch file and returns a Patch
         *
         * @param string $filename
         * @return Patch
         */
        static function from_file(string $filename) {
            // Check if file doesn't exists
            if (!file_exists($filename)) {
                throw new \Error("File {$filename} doesn't exists.");
            }

            // Load the file
            $content = file_get_contents($filename);

            // Check if doesn't contain any Lily patch
            if (strpos($content, "@lily") === -1) {
                throw new \Error("File {$filename} doesn't seems to be a valid patch.");
            }

            // Create the patch
            $patch = new self();

            // Retrieve all tokens
            $tokens = token_get_all($content);

            // Iterate over all tokens
            foreach($tokens as $token) {
                // Check if it's a string token
                if (is_string($token)) {
                    continue;
                }

                // Extract the token array
                list($id, $text) = $token;

                // Check if it's a multiline comment
                // and it contains a @lily annotation
                if (($id === T_ML_COMMENT || $id === T_DOC_COMMENT) && strpos($text, "@lily") > -1) {
                    // Parse it
                    preg_match_all(self::$comment_regex, $text, $matches, PREG_SET_ORDER);

                    // Check if matched nothing
                    if (empty($matches)) {
                        continue;
                    }

                    $task = null;
                    $params = new \stdclass;

                    // Iterate over all matches
                    foreach($matches as $match) {
                        // Parse the param name
                        $param = trim($match["param"]);

                        // Parse the arguments
                        $args = \Lily\Utils::string_commands_to_array($match["arguments"] ?? "");

                        // Save it as a param
                        $params->{$param} = $args;
                    }

                    // Check if has no task param
                    if (empty($params->task)) {
                        throw new \Error("Tried to create a task from a comment with no task type.");
                    }

                    // Try retrieving the class related to this task
                    $task_class = \Lily\Patcher::instance()->get_task($params->task);

                    // Check if it doesn't exists
                    if (!class_exists($task_class)) {
                        \Lily\Console::warn("The task named `{$params->task}` is not registered or doesn't exists.");
                        continue;
                    }

                    try {
                        // Create the task from the comment matches
                        $task = $task_class::from_comment($params);
                    } catch (\Error $e) {
                        \Lily\Console::error("An error ocurred while trying to create a task from a comment: " . $e->getMessage());
                        return $patch;
                    }

                    // Check if any task was created
                    if ($task !== null) {
                        // Add it to the patch
                        $patch->add_task($task);
                    }
                }
            }

            return $patch;
        }
    }