<?php

namespace Primavera\Framework;

use Primavera\Container\Container;
use Primavera\Container\ContainerBuilder;
use Primavera\Container\Processor\ComponentEnabler;
use Primavera\Framework\Component\HttpErrorHandler;
use Primavera\Framework\Component\Psr7Factory;
use Primavera\Framework\Configuration\Psr7Configuration;
use Primavera\Framework\Configuration\SerializerConfiguration;
use Primavera\Framework\Middleware\RequestBodyResolver;
use Primavera\Framework\Middleware\ResponseInterceptor;
use Primavera\Framework\Processor\ControllerStereotypeProcessor;
use Primavera\Framework\Processor\ErrorHandlerPostProcessor;
use Primavera\Framework\Processor\MiddlewareInterfaceStereotypeProcessor;
use Primavera\Framework\Processor\MiddlewareStereotypeProcessor;
use Primavera\Framework\Stereotype\UseMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Framework\Stereotype\ErrorHandler;
use Primavera\Framework\Stereotype\Formatter;
use Primavera\Framework\Stereotype\Interceptor;
use Primavera\Framework\Stereotype\Middleware;
use Primavera\Framework\Stereotype\ParamResolverInterface;
use Primavera\Framework\Stereotype\PreDispatch;
use Primavera\Framework\Stereotype\Service;

class Application
{
    protected ?ContainerBuilder $builder = null;

    protected ?Container $container = null;
    
    protected array $namespaces = [];

    protected ?string $appConfigFile = 'application.yaml';
    
    public function addNamespaces(string ...$namespaces) 
    {
        $this->namespaces = array_merge($this->namespaces, $namespaces);
        
        return $this;
    }

    public function withAppConfigFile(?string $configFile)
    {
        $this->appConfigFile = $configFile;

        return $this;
    }
    
    public function configure(callable $configure = null) {
        $builder = new ContainerBuilder();

        $builder
            // ->withAppNamespaces()
            ->withNamespaces(...$this->namespaces)
            ->withPreProcessors(
                new ComponentEnabler(Controller::class),
                new ComponentEnabler(ErrorHandler::class),
                new ComponentEnabler(Interceptor::class),
                new ComponentEnabler(Middleware::class),
                new ComponentEnabler(ParamResolverInterface::class),
                new ComponentEnabler(PreDispatch::class),
                new ComponentEnabler(Service::class),
                new ComponentEnabler(UseMiddleware::class),
            )
            ->withComponents(
                ControllerStereotypeProcessor::class,
                ErrorHandlerPostProcessor::class,
                MiddlewareStereotypeProcessor::class,
                MiddlewareInterfaceStereotypeProcessor::class,
                Psr7Configuration::class,
                SerializerConfiguration::class,
                Psr7Factory::class,
                HttpErrorHandler::class,
                RequestBodyResolver::class,
                ResponseInterceptor::class,
            );
        
        if ($this->appConfigFile)
            $builder->withConfigFile($this->appConfigFile);

        if ($configure) {
            $configure($builder);
        }

        $this->builder = $builder;
    }
    
    public function getBuilder(): ?ContainerBuilder
    {
        if (!$this->builder)
            $this->configure();

        return $this->builder;
    }

    public function getContainer(): Container {
        return $this->container ??= $this->getBuilder()->build();
    }

    public function run() {
        if (!$this->builder) {
            $this->configure();
        }

        $container = $this->getContainer();
        $container->get(App::class)->run();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $app = $this->getContainer()->get(App::class);

        return $app->handle($request);
    }
}