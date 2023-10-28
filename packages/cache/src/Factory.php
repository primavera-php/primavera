<?php

namespace Vox\Cache;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\RedisCache;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Vox\Cache\Bridge\DoctrineCacheBridge;

class Factory
{
    const TYPE_FILE = 'file';
    const TYPE_MEMORY = 'memory';
    const TYPE_REDIS = 'redis';
    const TYPE_APCU = 'apcu';
    const TYPE_MEMCACHED = 'memcached';

    const PROVIDER_DOCTRINE = 'doctrine';
    const PROVIDER_SYMFONY = 'symfony';

    private array $classMap = [
        self::PROVIDER_DOCTRINE => [
            self::TYPE_FILE => FilesystemCache::class,
            self::TYPE_MEMORY => ArrayCache::class,
            self::TYPE_REDIS => RedisCache::class,
            self::TYPE_APCU => ApcuCache::class,
            self::TYPE_MEMCACHED => MemcachedCache::class,
        ],
        self::PROVIDER_SYMFONY => [
            self::TYPE_FILE => FilesystemAdapter::class,
            self::TYPE_MEMORY => ArrayAdapter::class,
            self::TYPE_REDIS => RedisAdapter::class,
            self::TYPE_APCU => ApcuAdapter::class,
            self::TYPE_MEMCACHED => MemcachedAdapter::class,
        ],
    ];

    public function createSimpleCache(string $provider, string $type, ...$params): CacheInterface {
        if (!isset($this->classMap[$provider][$type])) {
            throw new \InvalidArgumentException("no cache implementation found for $provider => $type");
        }

        $class = $this->classMap[$provider][$type];

        $cache = new $class(...$params);

        if ($provider === self::PROVIDER_DOCTRINE) {
            return new DoctrineCacheBridge($cache);
        }

        return new Psr16Cache($cache);
    }
}