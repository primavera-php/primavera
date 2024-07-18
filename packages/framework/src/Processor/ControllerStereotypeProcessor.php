<?php

namespace Primavera\Framework\Processor;

use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Primavera\Framework\Stereotype\PreDispatch;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactory;
use Primavera\Metadata\MethodMetadataInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Primavera\Event\EventDispatcher;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Framework\Stereotype\Interceptor;
use Primavera\Framework\Stereotype\ParamResolverInterface;
use Primavera\Framework\Stereotype\UseMiddleware;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\Patch;
use Primavera\Http\Stereotype\Delete;


class ControllerStereotypeProcessor implements ComponentPostProcessorInterface
{
    use PrioritizedComponentsTrait;
    
    public function __construct(
        private MetadataFactory $metadataFactory,
        private EventDispatcher $eventDispatcher,
    ) { }

    public function canProcess(object $component): bool
    {
        return $this->metadataFactory
            ->getMetadataForClass($component::class)
            ->hasAnnotation(Controller::class);
    }

    private function processMiddleware(
        RouteInterface $route,
        MethodMetadataInterface $methodMetadata,
        ClassMetadataInterface $classMetadata,
        ContainerInterface $container,
    ) {
        foreach (array_filter([...$classMetadata->getAnnotations(), ...$methodMetadata->getAnnotations()], fn($a) => $a instanceof UseMiddleware) as $annotation) {
            $route->add(
                $container->get($annotation->middlewareClass)
            );
        }
    }

    private function parsePath($controller, $method)
    {
        return '/' . implode(
            '/',
            array_filter(
                array_map(fn($path) => preg_replace('/^\//', '', $path), [$controller->path, $method->path])
            )
        );
    }

    /**
     * @todo need to rethink how to get components by stereotype and improve getting prioritized components
     */
    public function process(object $stereotype, ContainerInterface $container)
    {
        /* @var $app App */
        $app = $container->get(App::class);

        /* @var $controllerMetadata ClassMetadataInterface */
        $controllerMetadata = $this->metadataFactory->getMetadataForClass(get_class($stereotype));

        /* @var $config \Primavera\Framework\Stereotype\Controller */
        $config = $controllerMetadata->getAnnotation(Controller::class);

        $methodMap = [
            Get::class => 'get',
            Post::class => 'post',
            Put::class => 'put',
            Patch::class => 'patch',
            Delete::class => 'delete',
        ];

        /* @var $methodMetadata MethodMetadata */
        foreach ($controllerMetadata->getMethodMetadata() as $methodMetadata) {
            $method = null;
            $methodName = null;

            foreach (array_keys($methodMap) as $currentMethod) {
                if ($methodMetadata->hasAnnotation($currentMethod)) {
                    $method = $methodMetadata->getAnnotation($currentMethod);
                    $methodName = $methodMap[$currentMethod];
                    break;
                }
            }

            if (null === $method || null === $methodName) {
                continue;
            }

            $path = $this->parsePath($config, $method);
            $action = $methodMetadata->getReflection()->getClosure($stereotype);

            $routeAction = function ($request, $response, $args) use ($controllerMetadata, $methodMetadata, $action,
                                                                      $container) {
                $params = $args;

                /* @var $resolver ParamResolverInterface */
                foreach ($container as $resolver) {
                    if (!$resolver instanceof ParamResolverInterface)
                        continue;

                    $params = array_merge(
                        $params,
                        $resolver->resolve($controllerMetadata, $methodMetadata, $request, $args)
                    );
                }

                foreach ($this->getPrioritizedComponents(PreDispatch::class, $container) as $preDispatch) {
                    $params = array_merge($params, $preDispatch($request, $controllerMetadata, $methodMetadata));
                }

                $actionParams = [];

                foreach ($methodMetadata->getParams() as $param) {
                    $actionParams[$param->getName()] ??= $params[$param->getName()] ?? null;
                }

                $responseData = call_user_func_array($action, array_values($actionParams));

                foreach ($this->getPrioritizedComponents(Interceptor::class, $container) as $interceptor) {
                    $response = $interceptor($responseData, $request, $response, $args);
                }

                 return $response;
            };

            $route = call_user_func([$app, $methodName], $path, $routeAction);
            $this->processMiddleware($route, $methodMetadata, $controllerMetadata, $container);
        }
    }
}