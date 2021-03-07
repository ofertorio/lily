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