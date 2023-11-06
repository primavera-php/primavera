<?php

namespace Primavera\Framework\Component;

use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Annotation\Value;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Primavera\Framework\Stereotype\ErrorHandler;
use Primavera\Framework\Exception\HttpNotFoundException;

/**
 * @ErrorHandler()
 */
class HttpErrorHandler {

    #[Value('debug', defaultValue: false)]
    private bool $debug = false;

    #[Autowired]
    private Psr7Factory $psr7Factory;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, Throwable $error) {
        switch (get_class($error)) {
            case HttpNotFoundException::class:
                return $response->withStatus(404, $error->getMessage());
            default:
                $response = $response->withStatus(500, $error->getMessage());

                if ($this->debug) {
                    $response = $this->psr7Factory->createResponse(500, $error);
                }

                return $response;
        }
    }
}
