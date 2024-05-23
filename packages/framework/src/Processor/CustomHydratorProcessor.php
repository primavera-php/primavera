<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\ContainerAwareInterface;
use Primavera\Data\ObjectHydratorInterface;
use Primavera\Data\Serializer;
use Primavera\Data\TypeAwareObjectHydrator;
use Primavera\Framework\Container\ContainerAwareTrait;

#[PostBeanProcessor]
class CustomHydratorProcessor implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke(Serializer $serializer) 
    {
        foreach ($this->getContainer()->getMetadadasByStereotype(TypeAwareObjectHydrator::class) as $id => $formatterMetadata) {
            $serializer->registerCustomHydrator($this->getContainer()->get($id));
        }
    }
}
