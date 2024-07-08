<?php

namespace Primavera\Container;

use Primavera\Metadata\MethodMetadataInterface;
use ReturnTypeWillChange;

interface ComponentFactoryStorageInterface
{
    #[ReturnTypeWillChange]
    public function addFactory(string $id, MethodMetadataInterface $factory);

    public function getFactory(string $id): MethodMetadataInterface;

    public function hasFactory(string $id): bool;
}
