<?php

namespace Primavera\Doctrine\Stereotype;

use Primavera\Container\Annotation\Imports;
use Primavera\Doctrine\Config\DoctrineConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([DoctrineConfiguration::class])]
class EnableDoctrineOrm
{

}