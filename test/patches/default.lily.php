<?php
    /**
     * Lily - Default test patch
     * 
     * @author Matheus Giovani <matheus@ad3com.com.br>
     * @since 1.0.0
     */

    /**
     * @lily will find and replace "test before" with "test after"
     * @task FindReplace
     * @files test.php
     * @find string "test before"
     * @replace "test after"
     */

    /**
     * @lily will remove all points inside a string
     * @task FindReplaceString
     * @files test.php
     * @find regex /(\.)/
     * @replace ""
     */

    /**
     * @lily will rename all variables from $variable_rename_test to $variable_renamed_test
     * @task RenameVar
     * @var $variable_rename_test
     * @rename $variable_renamed_test
     */

    /**
     * @lily will rename all functions names from function_that_will_be_renamed to function_that_was_renamed
     * @task RenameFunction
     * @function function_that_will_be_renamed
     * @rename function_that_was_renamed
     */

    /**
     * @lily will overwrite all functions named `function_that_will_be_refactored` with the function below
     * @task OverwriteFunction
     * @function function_that_will_be_refactored
     */
    function function_that_will_be_refactored() {
        echo "yayy! refactored :D";
    }