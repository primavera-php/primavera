<?php

namespace Primavera\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface 
{
    public function post(string $path, $body, array $headers = []);

    public function put(string $path, $body, array $headers = []);
    
    public function get(string $path, array $query = [], array $headers = []);
    
    public function delete(string $path, array $query = [], array $headers = []);

    public function send(RequestInterface $request): ResponseInterface;

    public function createRequest(string $method, string $path, array $headers = []);

    public function addBody(RequestInterface $request, string $body): RequestInterface;

    public function addQuery(RequestInterface $request, array $query): RequestInterface;
}
