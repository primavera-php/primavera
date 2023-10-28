<?php

namespace Vox\Framework\Test\Http;

use BadMethodCallException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vox\Framework\Application;
use Vox\Framework\Component\Http\HttpHandlerInterface;
use Vox\Framework\Component\Http\HttpPromiseInterface;

class HttpTestHandler implements HttpHandlerInterface {
    private Application $app;
    
    public function __construct(Application $app) {
        $this->app = $app;
    }
    
    public function send(RequestInterface $request, array $options = []): ResponseInterface {
        return $this->app->handle($request);
    }

    public function sendAsync(RequestInterface $request, array $options = []): HttpPromiseInterface {
        throw BadMethodCallException('not implemented');
    }

}
