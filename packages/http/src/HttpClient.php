<?php

namespace Primavera\Http;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class HttpClient implements HttpClientInterface, HttpClientAsyncInterface
{
    private HttpHandlerInterface $handler;
    
    public function __construct(HttpHandlerInterface $handler) 
    {
        $this->handler = $handler;
    }
    
    public function createRequest(string $method, string $path, array $headers = []): RequestInterface
    {
        $request = (new RequestFactory())
            ->createRequest($method, $path);
        
        foreach ($headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }
        
        return $request;
    }
    
    public function addBody(RequestInterface $request, string $body): RequestInterface 
    {
        return $request->withBody((new StreamFactory())->createStream($body));
    }

    public function addQuery(RequestInterface $request, array $query): RequestInterface
    {
        $query = http_build_query($query);

        return $request->withUri($request->getUri()->withQuery($query));
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->handler->send($request);
    }
    
    public function sendAsync(RequestInterface $request): HttpPromiseInterface
    {
        return $this->handler->sendAsync($request);
    }

    protected function createSend(string $method, string $path, $body, array $header = []) 
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }
        
        return $this->addBody($this->createRequest($method, $path), $body);
    }

    protected function createPost(string $path, $body, array $header = []) 
    {
        return $this->createSend('POST', $path, $body, $header);
    }

    protected function createPut(string $path, $body, array $header = []) 
    {
        return $this->createSend('PUT', $path, $body, $header);
    }

    protected function createGet(string $path, array $query, array $headers = []) 
    {
        return $this->addQuery($this->createRequest('GET', $path, $headers), $query);
    }

    protected function createDelete(string $path, array $query, array $headers = []) 
    {
        return $this->addQuery($this->createRequest('DELETE', $path, $headers), $query);
    }
    
    public function post(string $path, $body, array $headers = []) 
    {
        return $this->handler->send($this->createPost($path, $body, $headers));
    }

    public function put(string $path, $body, array $headers = []) 
    {
        return $this->handler->send($this->createPut($path, $body, $headers));
    }
    
    public function get(string $path, array $query = [], array $headers = []) 
    {
        return $this->handler->send($this->createGet($path, $query, $headers));
    }
    
    public function delete(string $path, array $query = [], array $headers = []) 
    {
        return $this->handler->send($this->createDelete($path, $query, $headers));
    }

    public function postAsync(string $path, $body, array $headers = []): HttpPromiseInterface
    {
        return $this->handler->sendAsync($this->createPost($path, $body, $headers));
    }

    public function putAsync(string $path, $body, array $headers = []): HttpPromiseInterface
    {
        return $this->handler->sendAsync($this->createPost($path, $body, $headers));
    }
    
    public function getAsync(string $path, array $query = [], array $headers = []): HttpPromiseInterface
    {
        return $this->handler->sendAsync($this->createGet($path, $query, $headers));
    }
    
    public function deleteAsync(string $path, array $query = [], array $headers = []): HttpPromiseInterface
    {
        return $this->handler->sendAsync($this->createDelete($path, $query, $headers));
    }

    public static function createWithGuzzleHandler(string $baseUri): self
    {
        return new self(new HttpGuzzleBridge(new Client(['base_uri' => $baseUri])));
    }
}
