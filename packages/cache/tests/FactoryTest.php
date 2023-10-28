<?php

namespace Vox\Cache;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Configuration\OptionsInterface;
use Symfony\Component\Cache\Psr16Cache;
use Vox\Cache\Bridge\DoctrineCacheBridge;

class FactoryTest extends TestCase
{
    public function testShouldCreateCache() {
        $factory = new Factory();

        $this->assertInstanceOf(
            DoctrineCacheBridge::class,
            $factory->createSimpleCache(Factory::PROVIDER_DOCTRINE, Factory::TYPE_FILE, '/tmp')
        );

        $this->assertInstanceOf(
            DoctrineCacheBridge::class,
            $factory->createSimpleCache(Factory::PROVIDER_DOCTRINE, Factory::TYPE_MEMORY)
        );

        $this->assertInstanceOf(
            DoctrineCacheBridge::class,
            $factory->createSimpleCache(Factory::PROVIDER_DOCTRINE,
                                        Factory::TYPE_REDIS, $this->createMock(Client::class))
        );

        $this->assertInstanceOf(
            DoctrineCacheBridge::class,
            $factory->createSimpleCache(Factory::PROVIDER_DOCTRINE,
                                        Factory::TYPE_APCU)
        );

        $this->assertInstanceOf(
            DoctrineCacheBridge::class,
            $factory->createSimpleCache(Factory::PROVIDER_DOCTRINE,
                                        Factory::TYPE_MEMCACHED)
        );

        $this->assertInstanceOf(
            Psr16Cache::class,
            $factory->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, 'tmp')
        );

        $this->assertInstanceOf(
            Psr16Cache::class,
            $factory->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_MEMORY)
        );

        $predisMock = $this->createMock(Client::class);
        $predisMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($this->createMock(OptionsInterface::class));

        $this->assertInstanceOf(
            Psr16Cache::class,
            $factory->createSimpleCache(Factory::PROVIDER_SYMFONY,
                                        Factory::TYPE_REDIS, $predisMock)
        );

        $this->assertInstanceOf(
            Psr16Cache::class,
            $factory->createSimpleCache(Factory::PROVIDER_SYMFONY,
                                        Factory::TYPE_APCU)
        );

        $this->assertInstanceOf(
            Psr16Cache::class,
            $factory->createSimpleCache(Factory::PROVIDER_SYMFONY,
                                        Factory::TYPE_MEMCACHED, $this->createMock(\Memcached::class))
        );
    }
}