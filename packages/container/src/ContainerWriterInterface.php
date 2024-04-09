<?php

namespace Primavera\Container;

interface ContainerWriterInterface 
{
    public function set(string $id, $value);
}
