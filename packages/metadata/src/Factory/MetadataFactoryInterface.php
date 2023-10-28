<?php

namespace Vox\Metadata\Factory;

use Vox\Metadata\ClassMetadataInterface;

/**
 * @template T of ClassMetadataInterface
 */
interface MetadataFactoryInterface
{
    /**
     * @return T
     */
    public function getMetadataForClass(string $className): ClassMetadataInterface;
}
