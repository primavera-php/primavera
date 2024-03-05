<?php

namespace ScannedTest\Factory;

use Primavera\Container\Factory\StereotypeFactoryInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Psr\Container\ContainerInterface;
use ScannedTest\Interfaces\InterfaceForBuilding;
use Shared\Stub\BuildingComponent;

/**
 * @extends StereotypeFactoryInterface<InterfaceForBuilding>
 */
class BuildingComponentFactory implements StereotypeFactoryInterface
{
    public function create(ContainerInterface $container, ClassMetadataInterface $metadata, array $params): InterfaceForBuilding
    {
        return new BuildingComponent(...$params);
    }
}
