<?php
/**
 * The module API is located in this file, which allows for creating custom modules.
 * Also see the {@link https://github.com/renfeisong/buaasoft-wechat/wiki/Module-API}
 * for more information and examples on how to create a module.
 *
 * @author Renfei Song
 * @since 1.0
 */

require_once "BaseModule.php";

function load_modules() {
    global $modules;
    $path = "../modules";
    foreach (new DirectoryIterator($path) as $fileInfo) {
        if ($fileInfo->isDot() == false && $fileInfo->isDir()) {
            $className = $fileInfo->getFilename();
            if (file_exists("../modules/" . $className . "/index.php")) {
                require_once "../modules/" . $className . "/index.php";
                if (class_exists($className)) {
                    $module = new $className;
                    if (is_subclass_of($module, "BaseModule")) {
                        array_push($modules, $module);
                    }
                }
            }
        }
    }

    usort($modules, "cmp");
}

function cmp(BaseModule $a, BaseModule $b) {
    if ($a->priority() == $b->priority())
        return 0;
    return ($a->priority() < $b->priority()) ? 1 : -1;
}