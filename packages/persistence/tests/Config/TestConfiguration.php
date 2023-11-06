<?php

namespace Vox\PersistenceTests\Config;

use Primavera\Container\Annotation\Configuration;
use Vox\Persistence\Stereotype\EnableDbal;
use Vox\Persistence\Stereotype\EnableDbalPersistence;

#[Configuration]
#[EnableDbal]
#[EnableDbalPersistence]
class TestConfiguration
{

}