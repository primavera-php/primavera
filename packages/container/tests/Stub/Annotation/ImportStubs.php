<?php

namespace Primavera\Container\Test\Stub\Annotation;
use Attribute;
use Primavera\Container\Annotation\Imports;
use Primavera\Container\Test\Stub\ImportStubsConfiguration;

#[Attribute(Attribute::TARGET_CLASS)]
#[Imports([
    ImportStubsConfiguration::class
])]
class ImportStubs
{
}
