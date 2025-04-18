<?php

namespace Primavera\PersistenceTests\Config;

use Primavera\Container\Annotation\Configuration;
use Primavera\Persistence\Stereotype\EnableDbal;
use Primavera\Persistence\Stereotype\EnableDbalPersistence;
use Primavera\Serializer\Annotation\EnableSerializer;

#[Configuration]
#[EnableDbal]
#[EnableDbalPersistence]
#[EnableSerializer]
class TestConfiguration
{

}