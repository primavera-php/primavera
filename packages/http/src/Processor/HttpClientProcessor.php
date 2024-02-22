<?php

namespace Primavera\Http\Processor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Primavera\Container\Bean\AbstractInterfaceImplementor;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Data\SerializerInterface;
use Primavera\Http\HttpClientInterface;
use Primavera\Http\Stereotype\Async;
use Primavera\Http\Stereotype\Delete;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Header;
use Primavera\Http\Stereotype\HttpClient;
use Primavera\Http\Stereotype\Patch;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\Query;
use Primavera\Http\Stereotype\RemoteFormat;
use Primavera\Http\Stereotype\RequestBody;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\MethodMetadata;
use Psr\Http\Message\ResponseInterface;

class HttpClientProcessor extends AbstractInterfaceImplementor
{
    public function getStereotypeName(): string
    {
        return HttpClient::class;
    }

    public function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadata $metadata, ClassMetadata $classMetadata): string
    {
        $codeLines = ['$args = get_defined_vars();', '$query = [];', '$headers = [];', '$data = null;'];
        $paramType = $metadata->getType();

        foreach ($metadata->params as $param) {
            if ($param->hasAnnotation(Query::class)) {
                $queryName = $param->getAnnotation(Query::class)->name ?? $param->name;
                $codeLines[] = "\$query[$queryName] = \${$param->getName()};";
            }

            if ($param->hasAnnotation(Header::class)) {
                $headerName = $param->getAnnotation(Header::class)->name ?? $param->name;
                $codeLines[] = "\$headers[$headerName] = \${$param->getName()};";
            }

            if ($param->hasAnnotation(RequestBody::class)) {
                if (!$paramType) {
                    throw new \InvalidArgumentException('RequestBody parameter must have a type');
                }

                $codeLines[] = "\$data = \$this->serializer->serialize('{$param->getAnnotation(RequestBody::class)->format}', \${$param->getName()});";
            }
        }

        $httpMethod = match(true) {
            $metadata->hasAnnotation(Get::class) => ['name' => 'GET', 'path' => $metadata->getAnnotation(Get::class)->path],
            $metadata->hasAnnotation(Post::class) => ['name' => 'POST', 'path' => $metadata->getAnnotation(Post::class)->path],
            $metadata->hasAnnotation(Put::class) => ['name' => 'PUT', 'path' => $metadata->getAnnotation(Put::class)->path],
            $metadata->hasAnnotation(Patch::class) => ['name' => 'PATCH', 'path' => $metadata->getAnnotation(Patch::class)->path],
            $metadata->hasAnnotation(Delete::class) => ['name' => 'DELETE', 'path' => $metadata->getAnnotation(Delete::class)->path],
            default => ['name' => 'GET', 'path' => $metadata->getName()],
        };
    
        $path = $httpMethod['path'] ?? $metadata->getName();
        $method = $metadata->hasAnnotation(Async::class) ? 'sendAsync' : 'send';
    
        $codeLines[] = "\$path = '{$path}';";
        $codeLines[] = '$path = preg_replace_callback(\'/\{([^}]+)\}/\', fn($m) => $args[$m[1]], $path);';
        $codeLines[] = "\$request = \$this->client->createRequest('{$httpMethod['name']}', \$path, \$headers);";
        $codeLines[] = 'if ($query) $request = $request->addQuery($request, $query);';
        $codeLines[] = 'if ($data) $request = $this->client->addBody($request, $data);';
        $codeLines[] = "\$response = \$this->client->{$method}(\$request);";

        $type = $metadata->getType();

        if ($type && !is_a($type, ResponseInterface::class, true)) {
            $format = $metadata->hasAnnotation(RemoteFormat::class) 
                ? $metadata->getAnnotation(RemoteFormat::class)->format 
                : 'json';

            $codeLines[] = "\$response = \$this->serializer->deserialize('$format', '$type', \$response->getBody());";
        }

        $codeLines[] = "return \$response;";

        return implode("\n", $codeLines);
    }

    public function postProcess(ClassMetadata $classMetadata, ClassGenerator $classGenerator)
    {
        $uri = $classMetadata->getAnnotation(HttpClient::class)->uri;

        $classGenerator->addProperty('client', flags: PropertyGenerator::FLAG_PRIVATE);
        $classGenerator->addProperty('serializer', flags: PropertyGenerator::FLAG_PRIVATE);
        $classGenerator->addMethod(
            '__construct',
            [
                ParameterGenerator::fromArray([
                    'name' => 'serializer',
                    'type' => SerializerInterface::class,
                ]),
                ParameterGenerator::fromArray([
                    'name' => 'client',
                    'type' => HttpClientInterface::class,
                    'defaultValue' => null,
                ]),
            ],
            body: "\$this->client = \$client ?? \Primavera\Http\HttpClient::createWithGuzzleHandler('$uri');\n\$this->serializer = \$serializer;",
        );
    }
}