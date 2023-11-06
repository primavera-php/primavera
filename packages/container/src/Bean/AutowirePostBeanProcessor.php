<?php

namespace Primavera\Container\Bean;

use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Container\Container;
use Primavera\Container\Container\ContainerException;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Container\Annotation\PostBeanProcessor;

/**
 * @PostBeanProcessor
 */
class AutowirePostBeanProcessor extends AbstractPropertyPostBeanProcessor
{
    public function getAnnotationClass(): string {
        return Autowired::class;
    }

    public function process(PropertyMetadata $property, $bean, Container $container, $annotation) {
        $type = $property->type;

        $dependency = $annotation->beanId ?: $type;

        if (!$dependency) {
            throw new ContainerException("Autowired must have a type or a bean id configured");
        }

        $property->setValue($bean, $container->get($dependency));
    }
}
