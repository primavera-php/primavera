<?php

namespace Vox\Persistence\Stereotype;

use Primavera\Container\Annotation\Imports;
use Vox\Persistence\Config\DbalConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([DbalConfiguration::class])]
class EnableDbal
{

}