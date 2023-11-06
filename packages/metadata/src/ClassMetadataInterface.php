<?php

namespace Primavera\Metadata;

/**
 * @template P
 * @template M
 */
interface ClassMetadataInterface extends MetadataInterface
{
    public function addMethodMetadata(MethodMetadataInterface $methodMetadata);
    
    public function addPropertyMetadata(PropertyMetadata $propertyMetadata);

    public function merge(ClassMetadataInterface $object): void;

    /**
     * @return M[]
     */
    public function getMethodMetadata(): array;

    /**
     * @return P[]
     */
    public function getPropertyMetadata(): array;

    /**
     * @return string[]
     */
    public function getFileResources(): array;

    /**
     * @return string[]
     */
    public function getInterfaces(): array;

    /**
     * @return string[]
     */
    public function getHierarchy(): array;

    /**
     * @return int
     */
    public function getCreatedAt(): int;

    public function isFresh(): bool;
}
