<?php

namespace Primavera\Metadata\Factory;

use IteratorAggregate;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\MethodMetadataInterface;

/**
 * @template T of ClassMetadataInterface<P, M>
 * @template P of PropertyMetadata
 * @template M of MethodMetadataInterface
 */
interface MetadataFactoryInterface extends IteratorAggregate
{
    /**
     * @return T<P, M>
     */
    public function getMetadataForClass(string $className): ClassMetadataInterface;

    public function loadFromFolder(string $folder = null): MetadataFactoryInterface;
}
