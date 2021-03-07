<?php
    $find_replace_test = "test after";

    $find_replace_regex_test = "remove .all. the. dots.";

    $variable_renamed_test = "this variable will be renamed";
    echo $variable_renamed_test . "{$variable_renamed_test}";

    function function_that_was_renamed() {
    }