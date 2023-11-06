<?php

namespace Primavera\Metadata;

/**
 * Trait to facilitate the annotations read from the metadata drivers
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
trait AnnotationsTrait
{
    public $annotations = [];
    
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function setAnnotations(array $annotations)
    {
        $this->annotations = $annotations;
        
        return $this;
    }
    
    /**
     * @template T
     * 
     * @param class-string<T> $annotationName
     * 
     * @return T | null
     */
    public function getAnnotation(string $annotationName, $throwException = false)
    {
        if (!isset($this->annotations[$annotationName]) && $throwException) {
            throw new \InvalidArgumentException("no annotation with name $annotationName");
        }
        
        return $this->annotations[$annotationName] ?? null;
    }
    
    public function hasAnnotation(string $annotationName)
    {
        return isset($this->annotations[$annotationName]);
    }
}
