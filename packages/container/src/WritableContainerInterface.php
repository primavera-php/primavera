<?php

namespace Primavera\Container;

use Psr\Container\ContainerInterface;

interface WritableContainerInterface extends ContainerInterface
{
    public function set(string $id, $value);
}
