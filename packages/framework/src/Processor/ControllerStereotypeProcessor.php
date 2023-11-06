<?php


namespace Vox\Framework\Processor;


use Vox\Metadata\Factory\MetadataFactory;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Vox\Event\EventDispatcher;
use Vox\Framework\Stereotype\Controller;
use Vox\Framework\Stereotype\Delete;
use Vox\Framework\Stereotype\Get;
use Vox\Framework\Stereotype\Interceptor;
use Vox\Framework\Stereotype\ParamResolverInterface;
use Vox\Framework\Stereotype\Patch;
use Vox\Framework\Stereotype\Post;
use Vox\Framework\Stereotype\PreDispatch;
use Vox\Framework\Stereotype\Put;
use Vox\Framework\Stereotype\UseMiddleware;
use Vox\Metadata\MethodMetadata;

class ControllerStereotypeProcessor extends AbstractStereotypeProcessor
{
    use PrioritizedComponentsTrait;
    
    private MetadataFactory $metadataFactory;

    private EventDispatcher $eventDispatcher;

    public function __construct(MetadataFactory $metadataFactory, EventDispatcher $eventDispatcher) {
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getStereotypeName(): string {
        return Controller::class;
    }

    private function processMiddleware(RouteInterface $route, MethodMetadata $methodMetadata,
                                       ClassMetadata $classMetadata) {
        if ($methodMetadata->hasAnnotation(UseMiddleware::class)) {
            $route->add(
                $this->getContainer()
                    ->get($methodMetadata->getAnnotation(UseMiddleware::class)->middlewareClass)
            );
        }

        if ($classMetadata->hasAnnotation(UseMiddleware::class)) {
            $route->add(
                $this->getContainer()
                    ->get($classMetadata->getAnnotation(UseMiddleware::class)->middlewareClass)
            );
        }
    }

    private function parsePath($controller, $method) {
        return '/' . implode(
            '/',
            array_filter(
                array_map(fn($path) => preg_replace('/^\//', '', $path), [$controller->path, $method->path])
            )
        );
    }

    public function process($stereotype) {
        /* @var $app App */
        $app = $this->getContainer()->get(App::class);

        /* @var $controllerMetadata ClassMetadata */
        $controllerMetadata = $this->metadataFactory->getMetadataForClass(get_class($stereotype));

        /* @var $config \Vox\Framework\Stereotype\Controller */
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
            $container = $this->getContainer();

            $routeAction = function ($request, $response, $args) use ($controllerMetadata, $methodMetadata, $action,
                                                                      $container) {
                $params = $args;

                /* @var $resolver ParamResolverInterface */
                foreach ($container->getComponentsByStereotype(ParamResolverInterface::class) as $resolver) {
                    $params = array_merge(
                        $params,
                        $resolver->resolve($controllerMetadata, $methodMetadata, $request, $args)
                    );
                }

                foreach ($this->getPrioritizedComponents(PreDispatch::class) as $preDispatch) {
                    $params = array_merge($params, $preDispatch($request, $controllerMetadata, $methodMetadata));
                }

                $actionParams = [];

                foreach ($methodMetadata->getParams() as $param) {
                    $actionParams[$param->name] ??= $params[$param->name] ?? null;
                }

                $responseData = call_user_func_array($action, array_values($actionParams));

                if ($responseData instanceof ResponseInterface) {
                    return $responseData;
                }


                foreach ($this->getPrioritizedComponents(Interceptor::class) as $interceptor) {
                    $response = $interceptor($responseData, $request, $response, $args);
                }

                 return $response;
            };

            $route = call_user_func([$app, $methodName], $path, $routeAction);
            $this->processMiddleware($route, $methodMetadata, $controllerMetadata);
        }
    }
}