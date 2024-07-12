<?php

namespace Primavera\Http\Annotation;

use Attribute;
use Primavera\Container\Annotation\Imports;
use Primavera\Http\Configuration\HttpConfiguration;

#[Attribute(Attribute::TARGET_CLASS)]
#[Imports([
    HttpConfiguration::class,
])]
class EnableHttp
{
}
