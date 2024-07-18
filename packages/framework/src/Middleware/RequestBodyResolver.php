<?php


namespace Primavera\Framework\Middleware;

use Primavera\Http\Stereotype\RequestBody;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;
use Psr\Http\Message\ServerRequestInterface;
use Primavera\Data\Serializer;
use Primavera\Framework\Stereotype\ParamResolverInterface;

class RequestBodyResolver implements ParamResolverInterface
{
    use GetAcceptsTrait;

    private Serializer $serializer;

    private string $defaultFormat;

    public function __construct(Serializer $serializer, string $defaultFormat = 'json')
    {
        $this->serializer = $serializer;
        $this->defaultFormat = $defaultFormat;
    }

    public function resolve(ClassMetadataInterface $controllerMetadata, MethodMetadataInterface $methodMetadata,
                            ServerRequestInterface $request, array $args): array {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            return [];
        }

        /* @var $paramsMetadata \Primavera\Metadata\ParamMetadata[] */
        $paramsMetadata = [];

        foreach ($methodMetadata->getParams() as $param) {
            $paramsMetadata[$param->getName()] = $param;
        }

        if (count($paramsMetadata) == 0) {
            return [];
        }

        $argName = reset($paramsMetadata)->getName();

        if ($requestBody = $methodMetadata->getAnnotation(RequestBody::class)) {
            $argName = $requestBody->argName ?? $argName;
        } elseif ($annotatedParams = array_filter($paramsMetadata, fn($p) => $p->getReflection()->getAttributes(RequestBody::class))) {
            if (count($annotatedParams) > 1) {
                throw new \LogicException("only one RequestBody allowed for method {$controllerMetadata->getName()}::{$methodMetadata->getName()}");
            }

            $argName = reset($annotatedParams)->name;
        } else {
            return [];
        }

        $type = $paramsMetadata[$argName]->getType() ?? $requestBody?->type;

        if (!$type) {
            throw new \LogicException("no type defined for param {$paramsMetadata[$argName]->getName()} on {$controllerMetadata->getName()}::{$methodMetadata->getName()}");
        }

        if (class_exists($type)) {
            $body = $this->serializer
                ->deserialize($this->getAcceptData($request) ?? $this->defaultFormat, $type, $request->getBody());
        } elseif (in_array($type, ['int', 'string', 'bool', 'float'])) {
            $body = match($type) {
                'int' => (int) $request->getBody(),
                'string' => (string) $request->getBody(),
                'bool' => (bool) $request->getBody(),
                'float' => (float) $request->getBody(),
            };
        } elseif($type === 'array') {
            $body = json_decode($request->getBody(), true);
        } else {
            throw new \LogicException("type {$type} is not supported for automatic body conversion");
        }

        return [$argName => $body];
    }
}