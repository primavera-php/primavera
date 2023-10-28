<?php

namespace Vox\Metadata\Test;

use PHPUnit\Framework\TestCase;
use Vox\Metadata\FunctionMetadata;

class FunctionMetadataTest extends TestCase
{
    public function testShouldCreateFunctionMetadata() {
        $metadata = new FunctionMetadata(new \ReflectionFunction(fn(string $a, int $b): int => $a + $b));

        $this->assertEquals('string', $metadata->params[0]->type);
        $this->assertEquals('int', $metadata->params[1]->type);
        $this->assertEquals('int', $metadata->type);
    }
}