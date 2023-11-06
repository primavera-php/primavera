<?php

namespace Primavera\Container\Metadata;

use Primavera\Container\Annotation\Injects;
use Primavera\Container\Container\ContainerException;
use Primavera\Metadata\ParamMetadata as BaseMetadata;

class ParamMetadata extends BaseMetadata
{
    public function getId()
    {
        $type = $this->getType();

        if (is_array($type) && !$this->hasAnnotation(Injects::class)) {
            throw new ContainerException(
                'Union types are not allowed without an alias; please use the Injects attribute'
            );
        }

        if ($this->hasAnnotation(Injects::class)) {
            return $this->getAnnotation(Injects::class)->beanId;
        }

        return !is_array($type) && !empty($type) && !$this->isNativeType()
            ? $type 
            : $this->name;
    }
}
