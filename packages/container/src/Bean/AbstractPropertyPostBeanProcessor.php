<?php


namespace Primavera\Container\Bean;


use Primavera\Container\Container\Container;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Metadata\PropertyMetadata;

abstract class AbstractPropertyPostBeanProcessor
{
    public function __invoke(Container $container)
    {
        foreach ($container as $id => $bean) {
            $metadata = $container->getMetadata($id);

            foreach ($metadata->getAnnotatedProperties($this->getAnnotationClass()) as $property) {
                $annotation = $property->getAnnotation($this->getAnnotationClass());

                $this->process($property, $bean, $container, $annotation);
            }
        }
    }

    public abstract function getAnnotationClass(): string;

    public abstract function process(PropertyMetadata $property, $bean, Container $container, $annotation);
}