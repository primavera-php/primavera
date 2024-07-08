<?php


namespace Primavera\Framework\Stereotype;


use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ParamResolverInterface
{
    public function resolve(ClassMetadataInterface $controllerMetadata, MethodMetadataInterface $methodMetadata,
                            ServerRequestInterface $request, array $args): array;
}