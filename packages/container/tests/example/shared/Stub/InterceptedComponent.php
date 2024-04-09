<?php


namespace Shared\Stub;

use Primavera\Container\Annotation\Component;
use Primavera\Container\Annotation\Injects;
use Primavera\Container\Annotation\Value;

class InterceptedComponent
{
    public function __construct(
        public int $value,
    ) {}

    public function getValue() {
        return $this->value;
    }
}