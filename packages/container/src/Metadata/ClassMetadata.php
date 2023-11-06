<?php

namespace Primavera\Container\Metadata;

use ReflectionClass;
use Vox\Metadata\ClassMetadata as BaseMetadata;
use Vox\Metadata\MethodMetadata;
use Vox\Metadata\ParamMetadata;
use Vox\Metadata\PropertyMetadata;

class ClassMetadata extends BaseMetadata
{
    /**
     * @return ParamMetadata[]
     */
    public function getConstructorParams(): array 
    {
        $constructor = $this->getConstructor();
        
        if (!$constructor) {
            return [];
        }

        return $constructor->params;
    }
    
    /**
     * @param string $annotation
     * 
     * @return MethodMetadata[]
     */
    public function getAnnotatedMethods(string $annotation) 
    {
        return array_filter(
            $this->methodMetadata,
            fn(MethodMetadata $metadata) => $metadata->hasAnnotation($annotation)
        );
    }

    /**
     * @param string $annotation
     * 
     * @return PropertyMetadata[]
     */
    public function getAnnotatedProperties(string $annotation) 
    {
        return array_filter(
            $this->propertyMetadata,
            fn(PropertyMetadata $metadata) => $metadata->hasAnnotation($annotation)
        );
    }

    public function isInstanceOf(string $class) 
    {
        return $this->implementsInterface($class)
            || $this->getReflection()->isSubclassOf($class)
            || $this->name == $class;
    }

    public function implementsInterface(string $interface) 
    {
        try {
            return $this->getReflection()->implementsInterface($interface);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getConstructor(): ?MethodMetadata 
    {
        $ctor = $this->getReflection()->getConstructor();

        return $this->methodMetadata[$ctor ? $ctor->name : null] ?? null;
    }
}
