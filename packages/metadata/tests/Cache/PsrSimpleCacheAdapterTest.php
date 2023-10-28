<?php


namespace Vox\Metadata\Test\Cache;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Vox\Metadata\Cache\PsrSimpleCacheAdapter;
use Vox\Metadata\ClassMetadata;
use Vox\Metadata\Factory\MetadataFactoryFactory;

class PsrSimpleCacheAdapterTest extends TestCase
{
    public function testShouldLoadMetadataFromCache() {
        $metadata = new ClassMetadata(new \ReflectionClass(SomeCacheStub::class));
        $metadata->fileResources[] = __FILE__;
        $metadata->createdAt = filemtime(__FILE__) - 1000;
        $cacheKey = "metadata." . str_replace('\\', '.', SomeCacheStub::class);

        $simpleCacheMock = $this->createMock(CacheInterface::class);
        $simpleCacheMock
            ->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn($metadata);

        $simpleCacheMock
            ->expects($this->exactly(3))
            ->method('has')
            ->with($cacheKey)
            ->willReturnOnConsecutiveCalls(false, false, true);

        // $simpleCacheMock
        //     ->expects($this->once())
        //     ->method('delete')
        //     ->with($cacheKey)
        //     ->willReturn($metadata);

        $simpleCacheMock
            ->expects($this->once())
            ->method('set')
            ->with($cacheKey, $this->anything());

        $metadataFactory = (new MetadataFactoryFactory(true))->createAnnotationMetadataFactory(cache: $simpleCacheMock);

        $metadata = $metadataFactory->getMetadataForClass(SomeCacheStub::class);
        $metadata = $metadataFactory->getMetadataForClass(SomeCacheStub::class);

        $this->assertEquals(SomeCacheStub::class, $metadata->name);
    }
}

class SomeCacheStub
{
    private $param1;

    private $param2;
}