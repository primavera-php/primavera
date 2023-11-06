<?php


namespace Primavera\Framework\Component;

use Primavera\Container\Annotation\Component;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Primavera\Data\Serializer;

/**
 * @Component()
 */
class Psr7Factory
{
    private ResponseFactoryInterface $responseFactory;

    private StreamFactoryInterface $streamFactory;

    private Serializer $serializer;

    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, Serializer $serializer)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->serializer = $serializer;
    }

    public function createResponse(int $status, $body, $format = 'json') {
        if ($body instanceof \Throwable) {
            $body = (string) $body;
        }

        switch (gettype($body)) {
            case 'string':
            case 'integer':
            case 'double':
            case 'boolean':
                $stream = $this->streamFactory->createStream($body);
                break;
            case 'resource':
                $stream = $this->streamFactory->createStreamFromResource($body);
                break;
            case "NULL":
                return $this->responseFactory->createResponse(204);
            default:
                $stream = $this->streamFactory->createStream($this->serializer->serialize($format, $body));
        }

        return $this->responseFactory->createResponse($status)->withBody($stream);
    }
}