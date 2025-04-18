<?php

namespace Primavera\Doctrine\Stereotype;

use Attribute;

/**
 * @template T
 */
#[Attribute(Attribute::TARGET_CLASS)]
class DoctrineRepository
{
    
    /**
     * @param class-string<T> $entityName
     */
    function __construct(
        public readonly string $entityName,
    ) {}
}
