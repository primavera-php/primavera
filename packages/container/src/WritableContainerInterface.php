<?php

namespace Primavera\Container;

use Psr\Container\ContainerInterface;
use ReturnTypeWillChange;

interface WritableContainerInterface extends ContainerInterface
{
    public function set(string $id, $value);
}
