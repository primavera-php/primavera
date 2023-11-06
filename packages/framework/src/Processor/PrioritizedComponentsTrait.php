<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Container\Container;
use Primavera\Framework\Collection\CallbackPriorityQueue;

trait PrioritizedComponentsTrait {
    private function getPrioritizedComponents(string $className, Container $container = null) {
        if (!$container) {
            $container = $this->getContainer();
        }
        
        return new CallbackPriorityQueue(
            function ($bean1, $bean2) use ($className) {
                $behavior1 = $this->metadataFactory->getMetadataForClass($bean1)->getAnnotation($className);
                $behavior2 = $this->metadataFactory->getMetadataForClass($bean2)->getAnnotation($className);

                return $behavior1->priority <=> $behavior2->priority;
            },
            $container->getComponentsByStereotype($className)
        );
    }
}
