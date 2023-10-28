<?php

namespace Vox\Metadata;

interface ClassMetadataInterface extends MetadataInterface
{
    public function addMethodMetadata(MethodMetadata $methodMetadata);
    
    public function addPropertyMetadata(PropertyMetadata $propertyMetadata);

    public function merge(ClassMetadataInterface $object): void;

    /**
     * @return MethodMetadata[]
     */
    public function getMethodMetadata(): array;

    /**
     * @return PropertyMetadata[]
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
}
