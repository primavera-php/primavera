<?php

namespace Primavera\PersistenceTests\Config;

use Primavera\Container\Annotation\Configuration;
use Primavera\Persistence\Stereotype\EnableDbal;
use Primavera\Persistence\Stereotype\EnableDbalPersistence;

#[Configuration]
#[EnableDbal]
#[EnableDbalPersistence]
class TestConfiguration
{

}