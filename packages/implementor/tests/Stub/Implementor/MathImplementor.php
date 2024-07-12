<?php

namespace Primavera\Implementor\Test\Stub\Implementor;

use Laminas\Code\Generator\MethodGenerator;
use Primavera\Implementor\AbstractInterfaceImplementor;
use Primavera\Implementor\Test\Stub\Annotation\Math;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;

class MathImplementor extends AbstractInterfaceImplementor
{
    protected function getStereotypeName(): string
    {
        return Math::class;
    }
    
    protected function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadataInterface $metadata, ClassMetadataInterface $classMetadata): string 
    {
        return 'return $a + $b;';
    }
}
