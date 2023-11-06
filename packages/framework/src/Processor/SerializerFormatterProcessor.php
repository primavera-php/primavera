<?php


namespace Vox\Framework\Processor;

use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\Container\Container;
use Primavera\Container\Container\ContainerAwareInterface;
use Vox\Data\Formatter\JsonFormatter;
use Vox\Data\Serializer;
use Vox\Framework\Stereotype\Formatter;
use Vox\Framework\Container\ContainerAwareTrait;

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