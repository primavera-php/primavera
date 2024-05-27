<?php

namespace Primavera\Data;

use Primavera\Metadata\Factory\MetadataFactoryInterface;
use RuntimeException;

/**
 * Accesses objects data through setters, getters and reflection
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class PropertyAccessor implements PropertyAccessorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;
    
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }
    
    public function get($object, string $name)
    {
        if (preg_match('/\./', $name)) {
            $properties = explode('.', $name);
            $name       = array_pop($properties);
            
            foreach ($properties as $property) {
                $object = $this->get($object, $property);
            }
        }
        
        $metadata = $this->metadataFactory->getMetadataForClass($object::class);
        $property = $metadata->getPropertyMetadata()[$name] 
            ?? throw new RuntimeException("property $name doesn't exists on {$metadata->getName()}");
        
        if ($property->hasGetter()) {
            return $property->getter->invoke($object);
        }
        
        return $property->getValue($object);
    }

    public function set($object, string $name, $value)
    {
        if (preg_match('/\./', $name)) {
            $properties = explode('.', $name);
            $name       = array_pop($properties);
            
            foreach ($properties as $property) {
                $object = $this->get($object, $property);
            }
        }
        
        $metadata = $this->metadataFactory->getMetadataForClass($object::class);
        $propertyMetadata = $metadata->getPropertyMetadata()[$name] 
            ?? throw new RuntimeException("property $name doesn't exists on {$metadata->getName()}");
        
        if ($propertyMetadata->hasSetter()) {
            $propertyMetadata->setter->invoke($object, $value);
        } else {
            $propertyMetadata->setValue($object, $value);
        }
    }

    public function tryGet(object $object, string $name, $defaultValue = null)
    {
        try {
            return $this->get($object, $name);
        } catch (\Error) {
            if ($defaultValue !== null) {
                $property = $this->metadataFactory
                    ->getMetadataForClass($object::class)
                    ->getPropertyMetadata()[$name] ?? null;
                
                if ($property?->getReflection()?->hasDefaultValue()) {
                    return $property->getReflection()->getDefaultValue();
                }
            }

            return $defaultValue;
        }
    }
}
