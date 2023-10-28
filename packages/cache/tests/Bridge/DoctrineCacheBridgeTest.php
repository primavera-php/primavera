<?php


namespace Vox\Cache\Bridge;


use Doctrine\Common\Cache\CacheProvider;
use PHPUnit\Framework\TestCase;

class DoctrineCacheBridgeTest extends TestCase
{
    public function testShouldDelegateCache() {
        $doctrineMock = $this->createMock(CacheProvider::class);

        $doctrineMock->expects($this->once())
            ->method('fetch');

        $doctrineMock->expects($this->once())
            ->method('save');

        $doctrineMock->expects($this->once())
            ->method('delete');

        $doctrineMock->expects($this->once())
            ->method('deleteAll');

        $doctrineMock->expects($this->once())
            ->method('fetchMultiple');

        $doctrineMock->expects($this->once())
            ->method('saveMultiple');

        $doctrineMock->expects($this->once())
            ->method('contains');

        $bridge = new DoctrineCacheBridge($doctrineMock);

        $bridge->set('foo', new \stdClass());
        $bridge->get('foo');
        $bridge->setMultiple(['foo' => new \stdClass(), 'bar' => []]);
        $bridge->getMultiple(['foo', 'bar']);
        $bridge->delete('foo');
        $bridge->deleteMultiple(['foo', 'bar']);
        $bridge->clear();
        $bridge->has('foo');
    }
}