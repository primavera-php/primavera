<?php

namespace Primavera\Metadata\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Metadata\FunctionMetadata;
use Primavera\Metadata\ParamMetadata;

function testFunction(string $param1) {

}

class ParamMetadataTest extends TestCase
{
    public function testShouldAcceptFunctionOrCallable() {
        $paramMetadata = (new FunctionMetadata(new \ReflectionFunction('Primavera\Metadata\Test\testFunction')))
            ->params[0];

        $this->assertEquals('string', $paramMetadata->type);

        $paramMetadata = (new FunctionMetadata(new \ReflectionFunction(fn(string $a, int $b): int => $a + $b)))
            ->params[0];

        $this->assertEquals('string', $paramMetadata->type);
    }
}
