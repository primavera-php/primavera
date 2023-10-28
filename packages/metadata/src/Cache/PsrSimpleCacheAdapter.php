<?php


namespace Vox\Metadata\Cache;


use Metadata\Cache\CacheInterface;
use Vox\Metadata\ClassMetadata;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

class PsrSimpleCacheAdapter implements CacheInterface
{
    private PsrCacheInterface $cache;

    private string $prefix;

    public function __construct(PsrCacheInterface $cache, string $prefix = 'metadata')
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    private function getClassName($class)
    {
        return str_replace('\\', '.', $class);
    }

    public function load(string $class): ?ClassMetadata
    {
        $className = $this->getClassName($class);
        return $this->cache->get("{$this->prefix}.{$className}");
    }

    public function put(ClassMetadata $metadata): void
    {
        $className = $this->getClassName($metadata->name);
        $this->cache->set("{$this->prefix}.{$className}", $metadata);
    }

    public function evict(string $class): void
    {
        $className = $this->getClassName($class);
        $key = "{$this->prefix}.{$className}";

        if ($this->cache->has($key)) {
            $this->cache->delete($key);
        }
    }
}