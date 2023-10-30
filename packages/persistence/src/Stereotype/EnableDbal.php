<?php

namespace Vox\Persistence\Stereotype;

use PhpBeans\Annotation\Imports;
use Vox\Persistence\Config\DbalConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([DbalConfiguration::class])]
class EnableDbal
{

}