<?php

namespace Primavera\Persistence\Stereotype;

use Primavera\Container\Annotation\Imports;
use Primavera\Persistence\Config\DbalConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([DbalConfiguration::class])]
class EnableDbal
{

}