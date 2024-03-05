<?php

declare(strict_types=1);

namespace Primavera\Doctrine\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Repository
{
    public function __construct(
        public readonly string $entityClass,
    ) {}
}
