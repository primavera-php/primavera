<?php


namespace Primavera\Container;


use Primavera\Container\Container;
use Primavera\Metadata\PropertyMetadata;

trait ValueProcessorTrait
{
    public function process(PropertyMetadata $property, $component, Container $container, $annotation)
    {
        $valueId = $annotation->componentId;

        try {
            $value = $container->get($valueId);

            if (!is_scalar($value)) {
                throw new \InvalidArgumentException("bean with id {$valueId} is not a scalar value to be used with @Value");
            }

            $property->isStatic() ? $property->setValue(null, $value) : $property->setValue($component, $value);
        } catch (\Throwable $e) {
            $this->logger->debug("cannot process value {$valueId}: {$e->getMessage()}");
            $property->isStatic() ? $property->setValue(null, $annotation->defaultValue) : $property->setValue($component, $annotation->defaultValue);
        }
    }
}