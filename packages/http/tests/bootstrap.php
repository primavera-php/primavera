<?php

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require 'vendor/autoload.php';
$loader->addPsr4('Primavera\\Http\\Tests\\', __DIR__);

if (strtolower(PHP_OS) === 'windows' || strtolower(PHP_OS) === 'winnt') {
    exec("rd /s /q build\cache");
} else {
    exec("rm -rf build/cache");
}