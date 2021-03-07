<?php
    /**
     * Lily - Development testing
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    require_once __DIR__ . "/../autoload.php";

    // Create a new Lily patcher
    $lily = new \Lily\Patcher();

    // Set the patcher working directory
    $lily->set_input_directory(__DIR__ . "/files/");
    $lily->set_output_directory(__DIR__ . "/result/");

    // Add the default test patch to it
    $lily->add_patch(__DIR__ . "/patches/default.lily.php");

    // Run the patch
    $lily->run();