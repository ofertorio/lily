<?php
    /**
     * Lily - Autoloader
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    // Register our own autoloader
	spl_autoload_register(function($item) {
        // Remove the leading slash from the item if any
        $item = ltrim($item, "\\");

        // Check if it's coming from our classpath
        if (strpos($item, "Lily") !== 0) {
            return;
        }

		// Replace camel case with "-"
		$file = preg_replace("/(?<=[a-zA-Z0-9])(?=[A-Z])/", "-$1", $item);

		// Get the classpath
		$classpath = str_replace("\\", "/", strtolower(str_replace("Lily\\", "", $file)));

		// Get the direct file name
		$file = __DIR__ . "\/includes\/" . $classpath . ".php";

		// Get the class file name too
        $class = dirname($file) . ".php";

        // Get the direct file name
        $class_file = __DIR__ . "/" . $classpath . "/" . basename($file);

        // Check if the complete file exists
        if (file_exists($file)) {
            require_once $file;
        } else
        // Check if the class file exists
        if (file_exists($class)) {
            require_once $class;
        } else
        // Check if classpath file exists
        if (file_exists($class_file)) {
            require_once $class_file;
        }
    });