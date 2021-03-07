<?php
    require_once __DIR__ . "/../autoload.php";

    $content = <<<CODE
        <?php
            function test() {
                echo "haha";
            }
    CODE;

    $patcher = new \Lily\Patcher();

    $patch = new \Lily\Patch;
    $patch->add_task(new class extends \Lily\Task {
        public function run(\Lily\File $file) {
            // This instruction should fail and throw an error
            $file->add_node_instruction($this, [
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
                ],
                "assert" => true
            ]);

            return $file;
        }
    });

    $patcher->add_patch($patch);
    $patcher->apply($content);