<?php

namespace Primavera\Serializer\Annotation;

use Attribute;
use Primavera\Container\Annotation\Imports;
use Primavera\Serializer\Configuration\SerializerConfiguration;

#[Attribute(Attribute::TARGET_CLASS)]
#[Imports([
    SerializerConfiguration::class
])]
class EnableSerializer
{
}
