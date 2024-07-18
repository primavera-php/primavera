<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Annotation\EventListener;
use Primavera\Container\Event\AfterContainerBuiltEvent;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Throwable;
use Primavera\Framework\Stereotype\ErrorHandler;

#[EventListener]
class ErrorHandlerPostProcessor
{
    use PrioritizedComponentsTrait;

    public function __construct() {}
    
    public function __invoke(AfterContainerBuiltEvent $event) {
        $container = $event->container;
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
