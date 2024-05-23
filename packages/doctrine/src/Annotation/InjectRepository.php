<?php

namespace Primavera\Doctrine\Annotation;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
class InjectRepository
{
    public function __construct(
        public string $id,
    ) {}
}
