<?php
    /**
     * Lily - Console manager
     * Allows running Lily via command line
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     * 
     * @todo make this happen!
     */

    namespace Lily;

    class Console {
        /**
         * Console text colors
         * 
         * @var array[string=>int]
         */
        const CONSOLE_COLORS= [
            "black" => 40,
            "red" => 31,
            "green" => 32,
            "yellow" => 433,
            "blue" => 34,
            "magenta" => 35,
            "cyan" => 36,
            "light-grey" => 37,
            "dark-gray" => 90,
            "light-red" => 91,
            "light-green" => 92,
            "light-yellow" => 93,
            "light-blue" => 94,
            "light-magenta" => 95,
            "light-cyan" => 96,
            "white" => 97
        ];

        /**
         * Console background colors
         * 
         * @var array[string=>int]
         */
        const CONSOLE_BACKGROUND_COLORS  = [
            "black" => 40,
            "red" => 41,
            "green" => 42,
            "yellow" => 43,
            "blue" => 44,
            "magenta" => 45,
            "cyan" => 46,
            "light-grey" => 47
        ];

        /**
         * Colorizes a string with a given color
         *
         * @param string $text The text to be colorized
         * @param string $color The color name
         * @param string $background=null The background color name if any
         * @return string
         */
        static function colorize($text, $color = "white", $background = null) {
            // Retrieve the color
            $color = self::CONSOLE_COLORS[$color] ?? $color;
            $background = $background ? ";" . self::CONSOLE_BACKGROUND_COLORS[$background] : "";

            return "\033[{$color}{$background}m{$text}\033[0m";
        }

        /**
         * Logs a message to the console
         *
         * @param mixed ...$args
         * @return void
         */
        static function log(...$args) {
            $prefix = self::colorize("[Lily]", "magenta");
            fwrite(STDOUT, $prefix . " " . implode(" ", $args) . PHP_EOL);
        }

        /**
         * Logs a warning message to the console
         *
         * @param mixed ...$args
         * @return void
         */
        static function warn(...$args) {
            return self::log(self::colorize("⚠️  [warn]", "light-yellow"), ...$args);
        }

        /**
         * Logs an error message to the console
         *
         * @param mixed ...$args
         * @return void
         */
        static function error(...$args) {
            return self::log(self::colorize("❌ [error]", "light-red"), ...$args);
        }

        /**
         * Logs an error message to the console
         *
         * @param mixed ...$args
         * @return void
         */
        static function debug(...$args) {
            return self::log(self::colorize("[debug]", "light-green"), ...$args);
        }

        private function commands($args) {
            array_shift( $args );
            $endofoptions = false;

            $ret = [
                'commands' => array(),
                'options' => array(),
                'flags'    => array(),
                'arguments' => array(),
            ];

            while ($arg = array_shift($args)) {
                // if we have reached end of options,
                //we cast all remaining argvs as arguments
                if ($endofoptions) {
                    $ret['arguments'][] = $arg;
                    continue;
                }

                // Check if it's a command (prefixed with --)
                if ( substr( $arg, 0, 2 ) === '--' ) {
                    // is it the end of options flag?
                    if (!isset ($arg[3])) {
                        $endofoptions = true; // end of options;
                        continue;
                    }

                    $value = "";
                    $com = substr( $arg, 2 );

                    // is it the syntax '--option=argument'?
                    if (strpos($com,'=')) {
                        list($com, $value) = split("=", $com, 2);
                    } else
                    // Is the option not followed by another option but by arguments
                    if (strpos($args[0],'-') !== 0) {
                        while (isset($args[0]) && strpos($args[0],'-') !== 0)
                        $value .= array_shift($args).' ';
                        $value = rtrim($value,' ');
                    }

                    $ret['options'][$com] = !empty($value) ? $value : true;
                    continue;
                }

                // Is it a flag or a serial of flags? (prefixed with -)
                if ( substr( $arg, 0, 1 ) === '-' ) {
                    for ($i = 1; isset($arg[$i]) ; $i++) {
                        $ret['flags'][] = $arg[$i];
                        continue;
                    }
                }

                // finally, it is not option, nor flag, nor argument
                $ret['commands'][] = $arg;
                continue;
            }

            if (!count($ret['options']) && !count($ret['flags'])) {
                $ret['arguments'] = array_merge($ret['commands'], $ret['arguments']);
                $ret['commands'] = array();
            }

            return $ret;
        }

        /**
         * Runs the console commands
         *
         * @return void
         */
        public function run() {
            global $argv;

            // Extract all command line arguments to commands
            $commands = $this->commands($argv);

            // Create the patcher
            $áthcer = new \Lily\Patcher();
        }
    }