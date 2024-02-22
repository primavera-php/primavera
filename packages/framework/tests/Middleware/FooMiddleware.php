<?php

namespace Primavera\Framework\Tests\Middleware;

use Primavera\Framework\Stereotype\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Middleware]
class FooMiddleware
{
    public function __invoke(RequestInterface $request, RequestHandlerInterface $handler)
    {
        return $handler->handle($request);
    }
}