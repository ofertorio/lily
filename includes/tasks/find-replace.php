<?php
    /**
     * Lily - Find & replace task
     * 
     * @author Matheus <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily\Tasks;

    class FindReplace extends \Lily\Task {
        const REQUIRED_PARAMS = ["find", "replace"];

        const NAME = "FindReplace";

        private $find = null;
        private $replace = null;

        public function __construct($params) {
            parent::__construct($params);

            $this->find = $params->find;
            $this->replace = $params->replace;
        }

        protected function run(\Lily\File $file) {
            $content = $file->get_content();

            // Extract the finding type
            $type = $this->find[0];

            // Extract the finding value
            $find = $this->find[1];

            // Switch the type
            switch($type) {
                // If it's a regex
                case "regex":
                    // Try matching the given regex against the content
                    $regex = strpos($find, "/") === 0 ? $find : ("/" . ltrim(rtrim($find, "/"), "/") . "/");

                    \Lily\Console::debug("replacing regex `{$regex}` with `{$this->replace}`");

                    // Replace it
                    $file->set_content(preg_replace($regex, $this->replace, $content));
                break;

                // If it's a normal string replace
                case "string":
                    \Lily\Console::debug("replacing string `{$find}` with `{$this->replace}`");

                    // Do the replacement
                    $file->set_content(str_replace($find, $this->replace, $content));
                break;
            }

            return $file;
        }
    }