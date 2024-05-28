<?php

namespace Primavera\Doctrine\Middleware;

use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Container\Metadata\ParamMetadata;
use Primavera\Data\SerializerInterface;
use Primavera\Doctrine\Annotation\Merge;
use Primavera\Framework\Exception\HttpNotFoundException;
use Primavera\Framework\Middleware\GetAcceptsTrait;
use Primavera\Framework\Stereotype\ParamResolverInterface;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\MethodMetadata;
use Psr\Http\Message\ServerRequestInterface;

#[IgnoreScanner]
class DoctrineMergeResolver implements ParamResolverInterface
{
    use GetAcceptsTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private string $defaultFormat = 'application/json',
    ) {}

    public function canIntercept(ParamMetadata $paramMetadata): bool
    {
        return $paramMetadata->hasAnnotation(Merge::class);
    }

    public function resolve(
        ClassMetadata $controllerMetadata,
        MethodMetadata $methodMetadata,
        ServerRequestInterface $request,
        array $args
    ): array {
        $resolved = [];

        foreach ($methodMetadata->params as $param) {
            if (!$param->hasAnnotation(Merge::class)) {
                continue;
            }

            $merge = $param->getAnnotation(Merge::class);

            $value = $args[$merge->param] 
                ?? throw new BadMethodCallException("no arg defined for this param {$param->getName()}:{$merge->param}");

            if (!$param->getType() || is_array($param->getType())) {
                throw new BadMethodCallException("the type for a merge must be of a single entity type");
            }

            $entity = $this->em->getRepository($param->getType())->findOneBy([$merge->findBy => $value]);

            if (!$entity) {
                throw new HttpNotFoundException("{$param->getType()} with {$merge->findBy} = {$value} not found for merge");
            }

            $this->serializer->deserialize($this->getAcceptData($request) ?? $this->defaultFormat, $entity, $request->getBody());

            $resolved[$param->name] = $entity;
        }

        return $resolved;
    }
}
