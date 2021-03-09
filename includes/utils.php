<?php
    /**
     * Lily - Util functions
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    namespace Lily;

    class Utils {
        /**
         * Converts snake case to camel case
         *
         * @param string $string The string to be converted
         * @param string [$sep="-"] The separator
         * @return string
         */
        static function snake_to_camel_case(string $string, string $sep = "-") {
            return ucfirst(str_replace($sep, "", ucwords($string, $sep)));
        }

        /**
         * Joins and resolves a path string
         *
         * @param string ...$path
         * @return string
         */
        static function path_resolve(string ...$path) {
            // Remove all trailing slashes from the arguments
            $path = array_map(function($p) {
                return rtrim(rtrim($p, "/"), "\\");
            }, $path);

            // Normalize the slashes
            $path = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $path));

            return $path;
        }

        /**
         * Convers a terminal command string to an array of arguments
         *
         * @param string $str
         * @return array[string]
         */
        static function string_commands_to_array(string $str) {
            // Match the arguments agains a shell argument regex
            preg_match_all("/(?:\"((?:(?<=\\\\)\"|[^\"])*)\"|'((?:(?<=\\\\)'|[^'])*)'|(\S+))/s", $str ?? "", $arg_matches, PREG_SET_ORDER);

            $args = [];

            // Remove the slashes from the matches
            foreach($arg_matches as $match) {
                if (isset($match[3])) {
                    $args[] = $match[3];
                } elseif (isset($match[2])) {
                    $args[] = str_replace(['\\\'', '\\\\'], ["'", '\\'], $match[2]);
                } else {
                    $args[] = str_replace(['\\"', '\\\\'], ['"', '\\'], $match[1]);
                }
            }

            return count($args) === 1 ? $args[0] : $args;
        }

        /**
         * Retrieves a directory as an array of file names
         *
         * @param string $dir
         * @return array[string]
         */
        static function directory_as_array(string $dir) {
            $final = [];

            // Create an iterator for the directory, skipping dot files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            // Iterate over all files
            foreach($files as $fileinfo) {
                $final[] = $fileinfo->getPathname();
            }

            return $final;
        }

        /**
         * Recursively delete a directory
         *
         * @param string $dir The directory to be deleted
         * @param boolean $keep_root If needs to keep the root directory
         * @return boolean
         */
        static function rrmdir(string $dir, $keep_root = false) {
            // Check if the directory already doesn't exists
            if (!is_dir($dir)) {
                return true;
            }

            // Create an iterator for the directory, skipping dot files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            // Iterate over all files
            foreach($files as $fileinfo) {
                // Check if it's a directory
                if ($fileinfo->isDir()) {
                    // Try deleting it
                    if (!@rmdir($fileinfo->getRealPath())) {
                        return false;
                    }
                } else {
                    if (!@unlink($fileinfo->getRealPath())) {
                        return false;
                    }
                }
            }

            // Delete the directory itself
            return $keep_root ? true : @rmdir($dir);
        }
    }