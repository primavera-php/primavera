<?php

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require 'vendor/autoload.php';
$loader->addPsr4('Primavera\\Doctrine\\Test\\', __DIR__);

// \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

if (strtolower(PHP_OS_FAMILY) === 'windows') {
    exec("rd /s /q build\cache");
    exec("rd /s /q build\generated");
} else {
    exec("rm -rf build/cache");
    exec("rm -rf build/generated");
}