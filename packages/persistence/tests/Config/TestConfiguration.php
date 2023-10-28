<?php

namespace Vox\PersistenceTests\Config;

use PhpBeans\Annotation\Configuration;
use Vox\Persistence\Stereotype\DbalPersistence;

#[Configuration]
#[DbalPersistence]
class TestConfiguration
{

}