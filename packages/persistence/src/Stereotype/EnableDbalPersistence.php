<?php

namespace Vox\Persistence\Stereotype;

use PhpBeans\Annotation\Imports;
use Vox\Persistence\Config\DbalPersistenceConfiguration;
use Vox\Persistence\Config\PersistenceConfiguration;

#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([PersistenceConfiguration::class, DbalPersistenceConfiguration::class])]
class EnableDbalPersistence
{

}
