<?php
    require_once __DIR__ . "/../autoload.php";

    $patcher = new \Lily\Patcher();

    $content = <<<CODE
        <?php
            function function_that_will_be_renamed() {
                echo "This long function name will be renamed to fn_renamed, isn't it great?";
            }
    CODE;

    $patch = new \Lily\Patch;
    $patch->add_task(new class extends \Lily\Task {
        public function run(\Lily\File $file) {
            // Rename all functions "function_that_will_be_renamed" to "fn_renamed"
            $file->add_node_instruction([
                "node" => "\PhpParser\Node\Stmt\Function_",
                "when" => "enter",
                "if" => [
                    function($node) {
                        return $node->name->toString() === "function_that_will_be_renamed";
                    }
                ],
                "do" => [
                    [
                        "action" => "set",
                        "vars" => [
                            "name" => "fn_renamed"
                        ]
                    ]
                ]
            ]);

            return $file;
        }
    });

    $patcher->add_patch($patch);

    echo $patcher->apply($content);