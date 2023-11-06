<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\Container\Container;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Throwable;
use Primavera\Framework\Stereotype\ErrorHandler;

/**
 * @PostBeanProcessor()
 */
class ErrorHandlerPostProcessor {
    use PrioritizedComponentsTrait;
    
    public function __invoke(Container $container) {
        /* @var $app App */
        $app = $container->get(App::class);
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        
        $handlers = $this->getPrioritizedComponents(ErrorHandler::class, $container);
        
        $errorMiddleware->setDefaultErrorHandler(
            function (ServerRequestInterface $request,
                      Throwable $exception) use ($app, $handlers) {
                $response = $app->getResponseFactory()->createResponse();

                foreach ($handlers as $handler) {
                    try {
                        $response = $handler($request, $response, $exception);
                    } catch (Throwable $e) {
                        // catch all
                    }
                }
                
                return $response;
            }
        );
    }
}
