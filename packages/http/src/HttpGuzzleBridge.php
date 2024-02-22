<?php

namespace Primavera\Http;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpGuzzleBridge implements HttpHandlerInterface 
{
    public function __construct(
        protected Client $client,
    ) { }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return $this->client->send($request, $options);
    }

    public function sendAsync(RequestInterface $request, array $options = []): HttpPromiseInterface
    {
        return new HttpPromiseGuzzleBridge($this->client->sendAsync($request, $options));
    }
}
