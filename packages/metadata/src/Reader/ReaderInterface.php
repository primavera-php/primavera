<?php

namespace Primavera\Metadata\Reader;

interface ReaderInterface
{
    public function getClassAnnotations(\ReflectionClass $class);
    
    public function getMethodAnnotations(\ReflectionMethod $method);
    
    public function getPropertyAnnotations(\ReflectionProperty $property);
}
