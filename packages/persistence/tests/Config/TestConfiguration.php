<?php

namespace Vox\PersistenceTests\Config;

use PhpBeans\Annotation\Configuration;
use Vox\Persistence\Stereotype\EnableDbalPersistence;

#[Configuration]
#[EnableDbalPersistence]
class TestConfiguration
{

}