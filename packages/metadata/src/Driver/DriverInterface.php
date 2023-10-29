<?php

namespace Vox\Metadata\Driver;

use Vox\Metadata\ClassMetadataInterface;
use Vox\Metadata\PropertyMetadata;
use Vox\Metadata\MethodMetadataInterface;

/**
 * @template T of ClassMetadataInterface<P,M>
 * @template P of PropertyMetadata
 * @template M of MethodMetadataInterface
 */
interface DriverInterface
{
    /**
     * @return T
     */
    public function loadMetadataForClass(\ReflectionClass $class): ClassMetadataInterface;
}
