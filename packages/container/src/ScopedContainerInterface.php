<?php

namespace Primavera\Container;

use Primavera\Metadata\MethodMetadataInterface;
use ReturnTypeWillChange;

interface ScopedContainerInterface
{
    /**
     * @template T
     * 
     * @param class-string<T> $className
     */
    #[ReturnTypeWillChange]
    public function addScoped(string $id, $className, MethodMetadataInterface $factory = null): self;
}
