<?php

namespace Primavera\Container\Processor;

use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Annotation\Value;
use Primavera\Container\Exception\NotFoundContainerException;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Psr\Container\ContainerInterface;

class AutowirePostProcessor implements ComponentPostProcessorInterface
{
    public function canProcess(object $component): bool 
    {
        return true;
    }
    
    public function process(object $component, ContainerInterface $container) 
    {
        $mf = $container->get(MetadataFactoryInterface::class);
        $metadata = $mf->getMetadataForClass($component::class);

        foreach ([Autowired::class, Value::class] as $attribute) {
            foreach ($metadata->getAnnotatedPropertiesMetadata($attribute) as $property) {
                $annot = $property->getAnnotation($attribute);

                $this->setPropertyValue($container, $property, $component, $annot);
            }
        }
    }

    private function setPropertyValue(ContainerInterface $container, $property, $object, $annotation)
    {
        $name = $annotation->id ?? (!$property->isNativeType() ? $property->getType() : null) ?? $property->name;

        try {
            $value = $container->get($name);
        } catch (NotFoundContainerException $e) {
            if ($property->isNativeType() && property_exists($annotation, 'defaultValue') && $annotation->defaultValue !== null) {
                $value = $annotation->defaultValue;
            } else {
                throw $e;
            }
        }

        $property->setValue($object, $value);
    }
}
