<?php

namespace Primavera\Framework\Test\Http;

use BadMethodCallException;
use Primavera\Http\HttpHandlerInterface;
use Primavera\Http\HttpPromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Primavera\Framework\Application;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

class HttpTestHandler implements HttpHandlerInterface 
{
    public function __construct(
        private Application $app,
    ) {}
    
    public function send(RequestInterface $request, array $options = []): ResponseInterface 
    {
        if (!$request instanceof ServerRequestInterface) {
            parse_str($request->getUri()->getQuery(), $query);

            $serverRequest = (new ServerRequestFactory)
                ->createServerRequest($request->getMethod(), $request->getUri())
                ->withBody($request->getBody())
                ->withQueryParams($query);

            foreach ($request->getHeaders() as $key => $value) {
                $serverRequest = $serverRequest->withHeader($key, $value);
            }

            $request = $serverRequest;
        }

        $response = $this->app->handle($request);

        if ($response->getStatusCode() >= 300) {
            throw new \Exception($response->getBody()->getContents());
        }

        return $response;
    }

    public function sendAsync(RequestInterface $request, array $options = []): HttpPromiseInterface 
    {
        throw new BadMethodCallException('not implemented');
    }
}
