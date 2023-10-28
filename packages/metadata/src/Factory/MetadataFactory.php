<?php

namespace Vox\Metadata\Factory;

use Psr\SimpleCache\CacheInterface;
use Vox\Metadata\ClassMetadataInterface;
use Vox\Metadata\Driver\DriverInterface;

class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * @var \Vox\Metadata\ClassMetadata[]
     */
    private array $metadatas = [];

    private $cachePrefix = 'metadata.';

    public function __construct(
        private DriverInterface $driver,
        private ?CacheInterface $cache = null,
    ) {}

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
}
