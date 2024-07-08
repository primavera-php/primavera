<?php

namespace Primavera\Commons\Collection;

use PHPUnit\Framework\TestCase;

class TreeCollectionTest extends TestCase
{
    public function testShouldManageTreeStructure()
    {
        $tree = new TreeCollection();

        $tree->addVertex('p1', 'c1');
        $tree->addVertex('p1', 'c2');
        $tree->addVertex('p1', 'c3');
        $tree->addVertex('p2', 'c3');
        $tree->addVertex('p2', 'c4');
        $tree->addVertex('c3', 'c4');
        $tree->addVertex('c3', 'n1');
        $tree->addVertex('c4', 'n2');
        $tree->addVertex('c1', 'n3');

        $expectedPaths = [
            ['p1', 'c1', 'n3'],
            ['p1', 'c2'],
            ['p1', 'c3', 'c4', 'n2'],
            ['p2', 'c3', 'c4', 'n2'],
            ['p2', 'c4', 'n2'],
            ['p1', 'c3', 'n1'],
            ['p2', 'c3', 'n1'],
        ];

        $this->assertEquals($expectedPaths, $tree->getPaths());
    }
}
