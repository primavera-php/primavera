<?php

namespace Primavera\Container;

use Primavera\Container\Metadata\ParamMetadata;
use Psr\Container\ContainerInterface;

interface ParamResolverInterceptorInterface
{
    public function canIntercept(ParamMetadata $paramMetadata): bool;

    #[\ReturnTypeWillChange]
    public function resolve(ParamMetadata $paramMetadata, ContainerInterface $container);
}
