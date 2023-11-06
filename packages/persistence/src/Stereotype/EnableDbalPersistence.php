<?php

namespace Primavera\Persistence\Stereotype;

use Primavera\Container\Annotation\Imports;
use Primavera\Persistence\Config\DbalPersistenceConfiguration;
use Primavera\Persistence\Config\PersistenceConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([PersistenceConfiguration::class, DbalPersistenceConfiguration::class])]
class EnableDbalPersistence
{

}
