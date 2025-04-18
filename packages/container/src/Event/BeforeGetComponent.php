<?php

namespace Primavera\Container\Event;
use Primavera\Container\Container;
use Primavera\Metadata\ParamMetadataInterface;

class BeforeGetComponent extends ComponentEvent
{
    public function __construct(
        Container $container,
        string $component,
        public readonly ?ParamMetadataInterface $paramMetadata = null,
    ) {
        parent::__construct($container, $component);
    }
}
