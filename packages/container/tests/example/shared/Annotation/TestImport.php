<?php


namespace Shared\Annotation;

use ScannedTest\Config\TestImportConfig;
use Primavera\Container\Annotation\Imports;


/**
 * @Annotation
 * @Imports({TestImportConfig::class})
 * @Target({"CLASS"})
 */
class TestImport
{

}