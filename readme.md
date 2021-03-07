Lily
===
This is a PHP 5.2 to PHP 8.0 patcher written in PHP. Its purpose is to simplify patching and manipulating PHP projects.

Quick Start
---

Lily can be integrated to your PHP code or task runner:

```php
<?php
    require_once __DIR__ . "/../autoload.php";

    $content = <<<CODE
        <?php
            echo "Hello world!";
    CODE;

    $patcher = new \Lily\Patcher();

    $patch = new \Lily\Patch;
    $patch->add_task(new class extends \Lily\Task {
        public function run(\Lily\File $file) {
            $file->set_content(str_replace("world", "planet", $file->get_content()));
            return $file;
        }
    });

    $patcher->add_patch($patch);

    echo $patcher->apply($content);
```

Lily also has a unique comment patching syntax that can be very useful if you have many patches to apply to a project.
Given Wordpress for example, it will rename all variables named `$post` to `$wp_post` in all files inside `ABSPATH`.

patch.php
```php
<?php
    /**
     * @lily
     * @task RenameVariable
     * @variable $post
     * @rename $wp_post
     */
```

```php
<?php
    $patcher = new \Lily\Patcher([
        "input_dir" => ABSPATH,
        "output_dir" => ABSPATH . "/patched"
    ]);

    $patcher->add_patch("patch.php");
    $patcher->run();
```

Node Instructions
---

You can pass node instructions to your patch tasks.
Node instructions are no-code friendly instructions that you can give to the PHPParser node visitor.

```php
<?php
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
                    "name" => ["==", "function_that_will_be_renamed"],
                    // Can also use a callable if wanted
                    function(\PhpParser\Node $node) {
                        return $node->name->toString() !== "function_that_cant_be_renamed";
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
```