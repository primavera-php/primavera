<?php

namespace Primavera\Container\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowired 
{
    public function __construct(
        public ?string $id = null,
    ) {}
}
