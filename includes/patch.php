<?php
    /**
     * Lily - Patch class
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    class Patch {
        /**
         * A comment regex with all valid annotation names
         * 
         * @var RegExp
         */
        static $comment_regex;

        static $parser;

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
        public function add_task($task, \PhpParser\Node $node) {
            // Check if it's a valid task
            if (!($task instanceof \Lily\Task)) {
                throw new Error("Not a valid Lily patch task, Lily is sad. 😞", "INVALID_PATCH");
            }

            // Set the related task node
            $task->node = $node;

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
                throw new Error("File {$filename} doesn't exists.", "FILE_NOT_FOUND");
            }

            // Load the file
            $content = file_get_contents($filename);

            // Check if doesn't contain any Lily patch
            if (strpos($content, "@lily") === -1) {
                throw new Error("File {$filename} doesn't seems to be a valid patch.", "INVALID_PATCH");
            }

            // Create the patch
            $patch = new self();

            $ast = null;

            if (static::$parser === null) {
                // Create a new parser if it doesn't exists
                static::$parser = (new \PhpParser\ParserFactory)->create(\PhpParser\ParserFactory::PREFER_PHP7);
            }

            try {
                // Parse it as an AST
                $ast = static::$parser->parse($content);
            } catch (\PhpParser\Error $e) {
                \Lily\Console::error("An error ocurred while parsing patch file `{$filename}`:\n" . $e->getMessage());
                return null;
            }

            // Create a node finder
            $node_finder = new \PhpParser\NodeFinder;

            $comment_nodes = [];
            $patches = [];

            // Find all multiline comments (possible patches)
            $comment_nodes = $node_finder->find($ast, function(\PhpParser\Node $node) {
                return $node->getComments();
            });

            // Iterate over all found nodes with comments
            foreach($comment_nodes as $comment_node) {
                // Retrieve the comments
                $comments = $comment_node->getComments();

                // Iterate over all comments
                foreach($comments as $comment) {
                    // Retrieve the comment text
                    $text = $comment->getText();

                    // Check if it's a Lily patch
                    if (strpos($text, "@lily") === -1) {
                        continue;
                    }

                    // Add it to the patches
                    $patches[] = (object) [
                        "node" => $comment_node,
                        "comment" => $text
                    ];
                }
            }

            // Iterate over all found patches
            foreach($patches as $found_patch) {
                // Parse the patch instructions
                preg_match_all(self::$comment_regex, $found_patch->comment, $matches, PREG_SET_ORDER);

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
                    throw new Error("Tried to create a task from a comment with no task type.", "INVALID_TASK");
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
                    $patch->add_task($task, $found_patch->node);
                }
            }

            return $patch;
        }
    }