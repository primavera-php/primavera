<?php


namespace Primavera\Framework\Middleware;

use Primavera\Container\Metadata\ClassMetadata;
use Psr\Http\Message\ServerRequestInterface;
use Primavera\Data\Serializer;
use Primavera\Framework\Stereotype\ParamResolverInterface;
use Primavera\Framework\Stereotype\RequestBody;
use Primavera\Metadata\MethodMetadata;

class RequestBodyResolver implements ParamResolverInterface
{
    private Serializer $serializer;

    private string $defaultFormat;

    public function __construct(Serializer $serializer, string $defaultFormat = 'json')
    {
        $this->serializer = $serializer;
        $this->defaultFormat = $defaultFormat;
    }

    public function resolve(ClassMetadata $controllerMetadata, MethodMetadata $methodMetadata,
                            ServerRequestInterface $request, array $args): array {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            return [];
        }

        /* @var $paramsMetadata \Primavera\Metadata\ParamMetadata[] */
        $paramsMetadata = [];

        foreach ($methodMetadata->params as $param) {
            $paramsMetadata[$param->name] = $param;
        }

        if (count($paramsMetadata) == 0) {
            return [];
        }

        $argName = reset($paramsMetadata)->name;

        if ($requestBody = $methodMetadata->getAnnotation(RequestBody::class)) {
            $argName = $requestBody->argName ?? $argName;
        } elseif ($annotatedParams = array_filter($paramsMetadata, fn($p) => $p->getReflection()->getAttributes(RequestBody::class))) {
            if (count($annotatedParams) > 1) {
                throw new \LogicException("only one RequestBody allowed for method {$controllerMetadata->name}::{$methodMetadata->name}");
            }

            $argName = reset($annotatedParams)->name;
        }

        $type = $paramsMetadata[$argName]->type ?? $requestBody?->type;

        if (!$type) {
            throw new \LogicException("no type defined for param {$paramsMetadata[$argName]->name} on {$controllerMetadata->name}::{$methodMetadata->name}");
        }

        $body = $this->serializer
            ->deserialize($this->defaultFormat, $type, $request->getBody());

        return [$argName => $body];
    }
}