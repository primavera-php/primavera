<?php

namespace Primavera\Metadata\Factory;

use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\MethodMetadataInterface;

/**
 * @template T of ClassMetadataInterface<P, M>
 * @template P of PropertyMetadata
 * @template M of MethodMetadataInterface
 */
interface MetadataFactoryInterface
{
    /**
     * @return T
     */
    public function getMetadataForClass(string $className): ClassMetadataInterface;
}
