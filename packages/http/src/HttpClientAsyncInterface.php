<?php

namespace Primavera\Http;
use Psr\Http\Message\RequestInterface;

interface HttpClientAsyncInterface {
    public function postAsync(string $path, $body, array $headers = []);

    public function putAsync(string $path, $body, array $headers = []);
    
    public function getAsync(string $path, array $query = [], array $headers = []);
    
    public function deleteAsync(string $path, array $query = [], array $headers = []);

    public function sendAsync(RequestInterface $request): HttpPromiseInterface;
}
