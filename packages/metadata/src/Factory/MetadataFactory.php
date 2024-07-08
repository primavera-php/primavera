<?php

namespace Primavera\Metadata\Factory;

use ArrayIterator;
use FilesystemIterator;
use Primavera\Metadata\FileReflection;
use Psr\SimpleCache\CacheInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Driver\DriverInterface;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\MethodMetadataInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Traversable;

/**
 * @template T of ClassMetadataInterface<P, M>
 * @template P of PropertyMetadata
 * @template M of MethodMetadataInterface
 * 
 * @extends \IteratorAggregate<T>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Primavera\Metadata\ClassMetadata[]
     */
    private array $metadatas = [];

    private $cachePrefix = 'metadata.';

    /**
     * @param DriverInterface<T> $name
     */
    public function __construct(
        private DriverInterface $driver,
        private ?CacheInterface $cache = null,
    ) {}
        
    /**
     * @return T
     */
    public function getMetadataForClass(string $className): ClassMetadataInterface
    {
        $cacheKey = $this->getCacheKey($className);

        if ($this->cache && $this->cache->has($cacheKey)) {
            $this->metadatas[$className] = $this->cache->get($cacheKey);
        }

        if (isset($this->metadatas[$className])) {
            return $this->metadatas[$className];
        }

        $this->metadatas[$className] = $metadata = $this->driver->loadMetadataForClass(new \ReflectionClass($className));

        if ($parent = $metadata->getReflection()->getParentClass()) {
            $parentMetadata = $this->getMetadataForClass($parent->name);
            $metadata->merge($parentMetadata);
        }

        if ($this->cache && !$this->cache->has($cacheKey)) {
            $this->cache->set($cacheKey, $metadata);
        }

        return $this->metadatas[$className];
    }

    private function getCacheKey(string $className)
    {
        return $this->cachePrefix . str_replace("\\", '.', $className);
    }

    public function loadFromFolder(string $folder = null): MetadataFactoryInterface
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder ?? getcwd(), FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            $file = new FileReflection($file);

            foreach ($file->getClasses() as $class) {
                $this->getMetadataForClass($class->name);
            }
        }

        return $this;
    }

    /**
     * @return T[]
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->metadatas);
    }
}
