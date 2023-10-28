<?php

namespace Vox\Metadata;

interface MetadataInterface extends \Serializable
{
    #[\ReturnTypeWillChange]
    public function getReflection(): \ReflectionClass | \ReflectionProperty | \ReflectionMethod | \ReflectionParameter | \ReflectionFunction;

    public function getAnnotations(): array;

    public function setAnnotations(array $annotations);
    
    /**
     * @template T
     * 
     * @param class-string<T> $annotationName
     * 
     * @return T | null
     */
    public function getAnnotation(string $annotationName, $throwException = false);
    
    public function hasAnnotation(string $annotationName);

    public function getName(): string;
}
