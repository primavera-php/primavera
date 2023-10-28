<?php

namespace Vox\Metadata\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Reads php 8 attributes and fallback to doctrines annotation reader
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class AttributeReader implements ReaderInterface
{
    private IndexedReader $reader;
    
    public function __construct() 
    {
        $this->reader = new IndexedReader(new AnnotationReader());
    }
    
    public function getClassAnnotations(ReflectionClass $class) 
    {
        $annotations = $this->reader->getClassAnnotations($class);
        
        return array_merge($annotations, $this->getAttributes($class));
    }
    
    public function getMethodAnnotations(ReflectionMethod $method) 
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        
        return array_merge($annotations, $this->getAttributes($method));
    }
    
    public function getPropertyAnnotations(ReflectionProperty $property) 
    {
        $annotations = $this->reader->getPropertyAnnotations($property);

        return array_merge($annotations, $this->getAttributes($property));
    }

    public function getAttributes(ReflectionClass | ReflectionMethod | ReflectionProperty | ReflectionParameter $reflection)
    {
        $attributes = [];
        
        foreach($reflection->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        return $attributes;
    }
}
