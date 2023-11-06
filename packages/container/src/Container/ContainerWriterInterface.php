<?php

namespace Primavera\Container\Container;

interface ContainerWriterInterface 
{
    public function set(string $id, $value);
}
