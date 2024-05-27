<?php

namespace Primavera\Doctrine\Annotation;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Merge
{
    public function __construct(
        public string $param = 'id',
        public string $findBy = 'id',
    ) {}
}