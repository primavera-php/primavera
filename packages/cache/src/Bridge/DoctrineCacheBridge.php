<?php

namespace Vox\Cache\Bridge;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Psr\SimpleCache\CacheInterface;

class DoctrineCacheBridge implements CacheInterface
{
    private CacheProvider $doctrineCache;

    public function __construct(Cache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    public function get($key, $default = null)
    {
        $data = $this->doctrineCache->fetch($key);

        if (false === $data) {
            return $default;
        }

        return $data;
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->doctrineCache->save($key, $value, $ttl ?? 0);
    }

    public function delete($key)
    {
        return $this->doctrineCache->delete($key);
    }

    public function clear()
    {
        return $this->doctrineCache->deleteAll();
    }

    public function getMultiple($keys, $default = null)
    {
        $items = $this->doctrineCache->fetchMultiple($keys);

        if (empty($items)) {
            return $default;
        }

        return $items;
    }

    public function setMultiple($values, $ttl = null)
    {
        $this->doctrineCache->saveMultiple($values, $ttl ?? 0);
    }

    public function deleteMultiple($keys)
    {
        return $this->doctrineCache->deleteMultiple($keys);
    }

    public function has($key)
    {
        return $this->doctrineCache->contains($key);
    }
}