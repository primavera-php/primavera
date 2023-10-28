<?php


namespace Vox\Framework\Middleware;

use PhpBeans\Annotation\Autowired;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vox\Framework\Stereotype\Interceptor;
use Vox\Framework\Component\Psr7Factory;

#[Interceptor]
class ResponseInterceptor
{
    #[Autowired]
    private Psr7Factory $psr7Factory;

    public function __invoke($responseData, ServerRequestInterface $request, ResponseInterface $response, array $args) {
        return $this->psr7Factory->createResponse(200, $responseData);
    }
}