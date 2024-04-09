<?php

namespace Shared\Stub;

use Primavera\Container\Annotation\Component;
use Shared\Annotation\Intercept;
use Shared\Stub\InterceptedComponent;

#[Component]
class ComponentWithInterceptedComponent
{
    public function __construct(
        #[Intercept]
        public InterceptedComponent $interceptedComponent,
    ) {}
}
