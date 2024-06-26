<?php

namespace Primavera\Framework;

use Primavera\Container\Container;
use Primavera\Container\Factory\ContainerBuilder;
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
            ->withStereotypes(
                Controller::class,
                Service::class,
                Middleware::class,
                PreDispatch::class,
                Interceptor::class,
                Formatter::class,
                ParamResolverInterface::class,
                ErrorHandler::class,
            )
            ->withAppNamespaces()
            ->withNamespaces(...$this->namespaces);
        
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