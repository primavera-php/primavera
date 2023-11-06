<?php

namespace Primavera\Metadata\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Metadata\Factory\MetadataFactoryFactory;

class ClassHierarchyTest extends TestCase
{
    public function testShouldReadEntireHierarchyAndInterfacesForClass()
    {
        $metadataFactory = (new MetadataFactoryFactory)->createAnnotationMetadataFactory();

        $childThree = $metadataFactory->getMetadataForClass(ChildThree::class);
        $childTwo = $metadataFactory->getMetadataForClass(ChildTwo::class);
        $childOne = $metadataFactory->getMetadataForClass(ChildOne::class);
        $root = $metadataFactory->getMetadataForClass(Root::class);

        $this->assertEquals(
            [
                Root::class,
                ChildOne::class,
                ChildTwo::class,
            ],
            $childThree->getHierarchy()
        );

        $this->assertEmpty(array_diff(
            [
                InterfaceOne::class,
                InterfaceTwo::class,
            ],
            $childThree->getInterfaces()
        ));

        $this->assertEquals(
            [
                Root::class,
                ChildOne::class,
            ],
            $childTwo->getHierarchy()
        );

        $this->assertEmpty(array_diff(
            [
                InterfaceOne::class,
                InterfaceTwo::class,
            ],
            $childTwo->getInterfaces()
        ));

        $this->assertEquals(
            [
                Root::class,
            ],
            $childOne->getHierarchy()
        );

        $this->assertEmpty(array_diff(
            [
                InterfaceOne::class,
                InterfaceTwo::class,
            ],
            $childOne->getInterfaces()
        ));

        $this->assertEmpty($root->getHierarchy());

        $this->assertEquals(
            [
                InterfaceOne::class,
            ],
            $root->getInterfaces()
        );

    }
}

interface InterfaceOne {}

interface InterfaceTwo {}

class Root implements InterfaceOne {}

class ChildOne extends Root implements InterfaceTwo {}

class ChildTwo extends ChildOne {}

class ChildThree extends ChildTwo {}
