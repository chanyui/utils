<?php

if (file_exists(dirname(__FILE__) . '/../../autoload.php')) {
    require_once dirname(__FILE__) . '/../../autoload.php';
}

function classLoader($class)
{
    $path = str_replace(['Chanyu\\Utils\\', '\\'], ['', DIRECTORY_SEPARATOR], $class);

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('classLoader');

require_once __DIR__ . '/src/Tools/AddrIdCard.php';