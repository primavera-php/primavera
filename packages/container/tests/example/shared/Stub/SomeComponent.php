<?php

namespace Shared\Stub;

use Primavera\Container\Annotation\Component;

#[Component('some-component')]
class SomeComponent
{
    public function getSomeName() {
        return 'some name';
    }
}