<?php

namespace Processor;

use Primavera\Container\Annotation\EventListener;
use Primavera\Container\Event\BeforeGetComponent;
use Primavera\Metadata\ParamMetadata;
use ReturnTypeWillChange;

#[EventListener]
abstract class AbstractParamInjector
{
    public function __invoke(BeforeGetComponent $event)
    {
        if (!$event->paramMetadata) {
            return;
        }

        if ($this->canInject($event->paramMetadata)) {
            $event->result = $this->inject($event->paramMetadata);
        }
    }

    protected abstract function canInject(ParamMetadata $paramMetadata): bool;

    #[ReturnTypeWillChange]
    protected abstract function inject(ParamMetadata $paramMetadata): object;
}
