<?php

namespace Primavera\Doctrine\Stereotype;

use Primavera\Container\Annotation\Imports;
use Primavera\Doctrine\Config\PrimaveraDoctrineConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([PrimaveraDoctrineConfiguration::class])]
class EnableDoctrineOrm
{

}