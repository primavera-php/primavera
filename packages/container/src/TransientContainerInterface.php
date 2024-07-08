<?php

namespace Primavera\Container;

use Primavera\Metadata\MethodMetadataInterface;

interface TransientContainerInterface
{
    /**
     * @template T
     * 
     * @param class-string<T> $className
     */
    public function addTransient(string $id, $className, MethodMetadataInterface $factory = null): self;
}
