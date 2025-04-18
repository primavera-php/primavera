<?php

namespace Primavera\Doctrine\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectRepository
{
    public function __construct(
        public string $entityClassName,
    ) {}
}
