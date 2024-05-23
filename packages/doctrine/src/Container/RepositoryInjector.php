<?php

namespace Primavera\Doctrine\Container;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Primavera\Container\ParamResolverInterceptorInterface;
use Primavera\Doctrine\Annotation\InjectRepository;
use Primavera\Metadata\ParamMetadata;
use Psr\Container\ContainerInterface;

class RepositoryInjector implements ParamResolverInterceptorInterface
{
    public function canIntercept(ParamMetadata $paramMetadata): bool
    {
        return $paramMetadata->hasAnnotation(InjectRepository::class);
    }

    public function resolve(ParamMetadata $paramMetadata, ContainerInterface $container): EntityRepository
    {
        return $container->get(EntityManagerInterface::class)
            ->getRepository($paramMetadata->getAnnotation(InjectRepository::class)->id);
    }
}
