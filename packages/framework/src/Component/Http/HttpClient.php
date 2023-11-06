<?php

namespace Primavera\Framework\Component\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class HttpClient implements HttpClientInterface, HttpClientAsyncInterface {
    private HttpHandlerInterface $handler;
    
    public function __construct(HttpHandlerInterface $handler) {
        $this->handler = $handler;
    }
    
    public function createRequest(string $method, string $path, array $headers = []) {
        $request = (new ServerRequestFactory())
            ->createServerRequest($method, $path);
        
        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }
        
        return $request;
    }
    
    public function addBody(ServerRequestInterface $request, string $body) {
        return $request->withBody((new StreamFactory())->createStream($body));
    }
    
    protected function createSend(string $method, string $path, $body, array $header = []) {
        if (is_array($body)) {
            $body = json_encode($body);
        }
        
        return $this->addBody($this->createRequest($method, $path), $body);
    }

    protected function createPost(string $path, $body, array $header = []) {
        return $this->createSend('POST', $path, $body, $header);
    }

    protected function createPut(string $path, $body, array $header = []) {
        return $this->createSend('PUT', $path, $body, $header);
    }

    protected function createGet(string $path, array $query, array $headers = []) {
        return $this->createRequest('GET', $path, $headers)->withQueryParams($query);
    }

    protected function createDelete(string $path, array $query, array $headers = []) {
        return $this->createRequest('DELETE', $path, $headers)->withQueryParams($query);
    }
    
    public function post(string $path, $body, array $headers = []) {
        return $this->handler->send($this->createPost($path, $body, $headers));
    }

    public function put(string $path, $body, array $headers = []) {
        return $this->handler->send($this->createPut($path, $body, $headers));
    }
    
    public function get(string $path, array $query = [], array $headers = []) {
        return $this->handler->send($this->createGet($path, $query, $headers));
    }
    
    public function delete(string $path, array $query = [], array $headers = []) {
        return $this->handler->send($this->createDelete($path, $query, $headers));
    }

    public function postAsync(string $path, $body, array $headers = []) {
        return $this->handler->sendAsync($this->createPost($path, $body, $headers));
    }

    public function putAsync(string $path, $body, array $headers = []) {
        return $this->handler->sendAsync($this->createPost($path, $body, $headers));
    }
    
    public function getAsync(string $path, array $query = [], array $headers = []) {
        return $this->handler->sendAsync($this->createGet($path, $query, $headers));
    }
    
    public function deleteAsync(string $path, array $query = [], array $headers = []) {
        return $this->handler->sendAsync($this->createDelete($path, $query, $headers));
    }
}
