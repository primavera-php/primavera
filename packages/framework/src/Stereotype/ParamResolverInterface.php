<?php


namespace Primavera\Framework\Stereotype;


use Primavera\Container\Metadata\ClassMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Primavera\Metadata\MethodMetadata;

interface ParamResolverInterface
{
    public function resolve(ClassMetadata $controllerMetadata, MethodMetadata $methodMetadata,
                            ServerRequestInterface $request, array $args): array;
}