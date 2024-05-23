<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\ContainerAwareInterface;
use Primavera\Data\ObjectExtractorInterface;
use Primavera\Data\Serializer;
use Primavera\Data\TypeAwareObjectExtractor;
use Primavera\Framework\Container\ContainerAwareTrait;

#[PostBeanProcessor]
class CustomExtractorProcessor implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke(Serializer $serializer) 
    {
        foreach ($this->getContainer()->getMetadadasByStereotype(TypeAwareObjectExtractor::class) as $id => $formatterMetadata) {
            $serializer->registerCustomExtractor($this->getContainer()->get($id));
        }
    }
}