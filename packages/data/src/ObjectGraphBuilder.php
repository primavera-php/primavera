<?php

namespace Primavera\Data;

use Primavera\Metadata\Factory\MetadataFactoryInterface;
use ReflectionClass;
use ReflectionParameter;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\PropertyMetadata;

/**
 * Use object's metadata to create the entire graph of empty objects in case there's no
 * instantiated objects on the properties marked as @var Type
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class ObjectGraphBuilder implements ObjectGraphBuilderInterface
{
    private $storage = [];
    
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    
    private $visited = [];

    public function __construct(MetadataFactoryInterface $metadataFactory, PropertyAccessorInterface $propertyAccessor)
    {
        $this->metadataFactory  = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
    }
    
    /**
     * @param object $object
     */
    public function buildObjectGraph($object)
    {
        if (is_string($object)) {
            $object = $this->createObject($object);
        }
        
        /* @var $metadata ClassMetadata */
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        
        /* @var $propertyMetadata PropertyMetadata */
        foreach ($metadata->propertyMetadata as $propertyMetadata) {
            $type = $propertyMetadata->type;
            
            if (!class_exists($type)) {
                continue;
            }
            
            $dependency = $this->fetchObject($type) ?? $this->createObject($type);
            
            if (!in_array($type, $this->visited)) {
                $this->visited[] = $type;
                $this->buildObjectGraph($dependency);
            }
            
            $this->propertyAccessor->set($object, $propertyMetadata->name, $dependency);
        }
        
        return $object;
    }
    
    private function createObject($className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        
        /* @var $reflection ReflectionClass */
        $reflection = $metadata->getReflection();
        
        $params = [];
        
        if ($constructor = $reflection->getConstructor()) {
            /* @var $injectable ReflectionParameter */
            foreach ($constructor->getParameters() as $injectable) {
                $typeReflection = $injectable->getType();

                if (!$typeReflection) {
                    continue;
                }

                $object = $this->fetchObject($typeReflection->getName())
                    ?? $this->createObject($typeReflection->getName());

                $this->storeObject($object);

                $params[] = $object;
            }
        }
        
        return $reflection->newInstanceArgs($params);
    }
    
    private function storeObject($object)
    {
        $this->storage[get_class($object)] = $object;
    }
    
    private function fetchObject(string $objectName)
    {
        return $this->storage[$objectName] ?? null;
    }

    public function clear()
    {
        $this->storage = $this->visited = [];
        
        return $this;
    }
}
