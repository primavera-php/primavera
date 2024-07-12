<?php

namespace Primavera\Container;

use Primavera\Container\Processor\ComponentPreProcessorInterface;
use Primavera\Event\EventDispatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

interface ContainerBuilderInterface
{
    public function shouldScanComponents(bool $should): ContainerBuilderInterface;

    public function withAllNamespaces(): ContainerBuilderInterface;

    public function withAppNamespaces(): ContainerBuilderInterface;

    public function withNamespaces(string ...$namespaces): ContainerBuilderInterface;

    public function withPreProcessors(ComponentPreProcessorInterface ...$processors): ContainerBuilderInterface;

    public function withYamlMetadata(string $metadataPath): ContainerBuilderInterface;

    public function withInstances(array $instances): ContainerBuilderInterface;

    public function withEventDispatcher(EventDispatcherInterface $eventDispatcher): ContainerBuilderInterface;

    public function withCache(CacheInterface $cache): ContainerBuilderInterface;

    public function withComponents(string ...$components): ContainerBuilderInterface;

    public function withFactories(array $factories): ContainerBuilderInterface;

    public function withConfigFile(string $configFile): ContainerBuilderInterface;

    public function build(): ContainerInterface;
}
