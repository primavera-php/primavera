<?php


namespace Primavera\Framework\Processor;

use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\Container\Container;
use Primavera\Container\Container\ContainerAwareInterface;
use Primavera\Data\Formatter\JsonFormatter;
use Primavera\Data\Serializer;
use Primavera\Framework\Stereotype\Formatter;
use Primavera\Framework\Container\ContainerAwareTrait;

#[PostBeanProcessor]
class SerializerFormatterProcessor implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke(Serializer $serializer) {
        $serializer->registerFormat(new JsonFormatter());

        foreach ($this->getContainer()->getMetadadasByStereotype(Formatter::class) as $id => $formatterMetadata) {
            $serializer->registerFormat(
                $this->getContainer()->get($id),
                $formatterMetadata->getAnnotation(Formatter::class)->format
            );
        }
    }
}