<?php

namespace Vox\Persistence\Stereotype;

use PhpBeans\Annotation\Imports;
use Vox\Persistence\Config\DbalPersistenceConfiguration;
use Vox\Persistence\Config\PersistenceConfiguration;

/**
 * @Annotation
 * @Target({'CLASS'})
 * @Imports({PersistenceConfiguration::class, DbalPersistenceConfiguration::class})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
#[Imports([PersistenceConfiguration::class, DbalPersistenceConfiguration::class])]
class DbalPersistence
{

}
