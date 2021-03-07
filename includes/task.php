<?php
    /**
     * Lily - Task class
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    class Task {
        const REQUIRED_PARAMS = [];
        const OPTIONAL_PARAMS = [];

        /**
         * The task name
         *
         * @var string
         */
        public $name = null;

        /**
         * The current task file
         *
         * @var File
         */
        protected $current_file = null;

        /**
         * The file names that the task will ran on
         *
         * @var array[string]
         */
        protected $files = null;

        public function __construct(object $params = null) {
            // Check if any file array was given
            if (!empty($params->files)) {
                // Force it to be an array
                $params->files = (array) $params->files;

                // Save them
                $this->files = $params->files;
            }
        }

        /**
         * Creates a new task from a parsed comment structure
         *
         * @param object $params The parsed comment parameters
         * @return Task
         */
        static function from_comment(object $params) {
            // Retrieve the task name
            $name = static::NAME ?? get_called_class();

            // Iterate over all required task parameters
            foreach(static::REQUIRED_PARAMS as $param) {
                // Check if has no "find" or "do"
                if (!isset($params->{$param})) {
                    throw new \Error("Missing param `{$param}` for task `{$name}`.");
                }
            }

            // Create the task
            $task = new static($params);
            $task->name = $name;

            return $task;
        }

        /**
         * Returns the task name
         *
         * @return void
         */
        public function get_name() {
            return $this->name;
        }

        /**
         * Checks if this tasks runs on specific files
         *
         * @return boolean
         */
        public function has_files() {
            return !empty($this->files);
        }

        /**
         * Retrieves all files that this task will run on
         *
         * @return array[string]
         */
        public function get_files() {
            return $this->files;
        }

        /**
         * Parse the current contents tokens
         *
         * @return array[Token]
         */
        public function get_tokens() {
            // @todo cache the tokens somehow
            return token_get_all($this->current_file->get_content());
        }

        /**
         * Runs the task for a determinated content
         *
         * @param \Lily\File|string $file The file or file name
         * @return string
         */
        public function apply($file = null) {
            // Check if $file is a string and file
            if (is_string($file)) {
                // Check if file doesn't exists
                if (!is_file($file)) {
                    throw new \Error("File {$file} doesn't exists.");
                }

                // Retrieve the file content handler
                $file = new \Lily\File($file);
            }

            // Save a reference to the current file
            $this->current_file = &$file;

            // Run the task
            return $this->run($file);
        }

        protected function run(\Lily\File $file) {
            throw new \Error("Task {$this->name} still doesn't have a run() function.");
        }
    }