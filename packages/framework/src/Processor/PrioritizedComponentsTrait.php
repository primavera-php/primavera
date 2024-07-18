<?php

namespace Primavera\Framework\Processor;

use IteratorAggregate;
use Primavera\Framework\Collection\CallbackPriorityQueue;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Psr\Container\ContainerInterface;

trait PrioritizedComponentsTrait 
{
    private function getPrioritizedComponents(string $className, ContainerInterface & IteratorAggregate $container)
    {
        $components = [];
        $mf = $container->get(MetadataFactoryInterface::class);

        foreach ($container as $component) {
            $metadata = $mf->getMetadataForClass($component::class);

            if ($metadata->hasAnnotation($className)
                || $metadata->instanceof($className))
                $components[] = $component;
        }

        return new CallbackPriorityQueue(
            function ($bean1, $bean2) use ($className, $mf) {
                $behavior1 = $mf->getMetadataForClass($bean1::class)->getAnnotation($className);
                $behavior2 = $mf->getMetadataForClass($bean2::class)->getAnnotation($className);

                return $behavior1->priority <=> $behavior2->priority;
            },
            $components
        );
    }
}
