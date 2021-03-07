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

        const STATUS_NOT_EXECUTED = 0;
        const STATUS_SUCCEEDED = 1;
        const STATUS_FAILED = -1;

        /**
         * The number of times that this task was concluded
         *
         * @var integer
         */
        private $concluded = 0;

        /**
         * The task status
         *
         * @var int
         */
        private $status = self::STATUS_NOT_EXECUTED;

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

        /**
         * Creates a new task from a parsed comment structure
         *
         * @param object $params The parsed comment parameters
         * @throws \Lily\Error
         * @return Task
         */
        static function from_comment(object $params) {
            // Retrieve the task name
            $name = static::NAME ?? get_called_class();

            // Iterate over all required task parameters
            foreach(static::REQUIRED_PARAMS as $param) {
                // Check if has no "find" or "do"
                if (!isset($params->{$param})) {
                    throw new Error("Missing param `{$param}` for task `{$name}`.", "MISSING_PARAM");
                }
            }

            // Create the task
            $task = new static($params);
            $task->name = $name;

            return $task;
        }

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
         * Retrieves the task status
         *
         * @return int
         */
        public function get_status() {
            return $this->status;
        }

        /**
         * Stops the patcher execution
         *
         * @return void
         */
        public function stop() {
            return \Lily::instance()->stop();
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
         * @throws \Lily\Error
         * @return string
         */
        public function apply($file = null) {
            // Check if $file is a string and file
            if (is_string($file)) {
                // Check if file doesn't exists
                if (!is_file($file)) {
                    throw new Error("File {$file} doesn't exists.", "FILE_NOT_FOUND");
                }

                // Retrieve the file content handler
                $file = new \Lily\File($file);
            }

            // Save a reference to the current file
            $this->current_file = &$file;

            // Run the task
            return $this->run($file);
        }

        /**
         * Succeeds the current task
         *
         * @return void
         */
        public function succeed() {
            // Increase the task concluded times
            $this->concluded++;
        }

        /**
         * Fails the current task
         *
         * @throws \Lily\Error
         * @return void
         */
        public function fail(int $reason, $data = null) {
            $this->status = self::STATUS_FAILED;
        }

        protected function run(\Lily\File $file) {
            $this->status = self::STATUS_NOT_EXECUTED;
            throw new Error("Task {$this->name} still doesn't have a run() function", "NOT_IMPLEMENTED_YET");
        }
    }