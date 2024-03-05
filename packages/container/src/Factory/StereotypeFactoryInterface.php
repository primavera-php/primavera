<?php

namespace Primavera\Container\Factory;

use Primavera\Metadata\ClassMetadataInterface;
use Psr\Container\ContainerInterface;
use ReturnTypeWillChange;

/**
 * @template T
 */
interface StereotypeFactoryInterface
{
    /**
     * @return T
     */
    #[ReturnTypeWillChange]
    public function create(ContainerInterface $container, ClassMetadataInterface $metadata, array $params);
}
