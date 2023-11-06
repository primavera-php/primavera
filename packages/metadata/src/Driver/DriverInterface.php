<?php

namespace Primavera\Metadata\Driver;

use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\MethodMetadataInterface;

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
