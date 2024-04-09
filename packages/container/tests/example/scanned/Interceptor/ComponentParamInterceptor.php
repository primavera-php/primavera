<?php

namespace ScannedTest\Interceptor;

use Primavera\Container\ParamResolverInterceptorInterface;
use Primavera\Metadata\ParamMetadata;
use Psr\Container\ContainerInterface;
use Shared\Annotation\Intercept;
use Shared\Stub\InterceptedComponent;

class ComponentParamInterceptor implements ParamResolverInterceptorInterface
{
    public function canIntercept(ParamMetadata $paramMetadata): bool
    {
        return $paramMetadata->hasAnnotation(Intercept::class);
    }

    public function resolve(ParamMetadata $paramMetadata, ContainerInterface $container): InterceptedComponent
    {
        return new InterceptedComponent(20);
    }
}
