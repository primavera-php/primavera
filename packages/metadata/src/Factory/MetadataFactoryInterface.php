<?php

namespace Vox\Metadata\Factory;

use Vox\Metadata\ClassMetadataInterface;
use Vox\Metadata\PropertyMetadata;
use Vox\Metadata\MethodMetadataInterface;

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
