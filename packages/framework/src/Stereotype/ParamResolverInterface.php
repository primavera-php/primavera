<?php


namespace Vox\Framework\Stereotype;


use PhpBeans\Metadata\ClassMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Vox\Metadata\MethodMetadata;

interface ParamResolverInterface
{
    public function resolve(ClassMetadata $controllerMetadata, MethodMetadata $methodMetadata,
                            ServerRequestInterface $request, array $args): array;
}