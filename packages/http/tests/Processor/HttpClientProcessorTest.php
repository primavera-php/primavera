<?php

namespace Primavera\Http\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Predis\Response\ResponseInterface;
use Primavera\Cache\Factory;
use Primavera\Container\Bean\AbstractInterfaceImplementor;
use Primavera\Container\Container\Container;
use Primavera\Container\Factory\ContainerBuilder;
use Primavera\Http\HttpClientInterface;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\HttpClient;
use Prophecy\Prophet;
use Psr\Http\Message\RequestInterface;

class HttpClientProcessorTest extends TestCase
{
    private Container $container;

    private $httpClientProfecy;

    private $httpRequestProfecy;

    private $httpResponseProfecy;

    private HttpClientInterface $httpClientMock;

    private Prophet $mocker;

    public function setUp(): void
    {
        $this->mocker = $mocker = new Prophet();
        
        $cb = new ContainerBuilder();

        $cb->withAppNamespaces()
            ->withNamespaces('Primavera\\Http\\Tests\\')
            ->withStereotypes(HttpClient::class, AbstractInterfaceImplementor::class)
            ->withCache(
                (new Factory)
                    ->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, '', 0, 'build/cache')
            );

        $this->httpClientProfecy = $mocker->prophesize(HttpClientInterface::class);
        $this->httpRequestProfecy = $mocker->prophesize(RequestInterface::class);
        $this->httpResponseProfecy = $mocker->prophesize(ResponseInterface::class);
        $this->httpClientMock = $this->httpClientProfecy->reveal();

        $cb->withBeans([
            HttpClientInterface::class => $this->httpClientMock
        ]);

        $this->container = $cb->build();
    }

    public function testShouldImplemtnClient()
    {
        $httpClientMock = $this->httpClientMock;
        $httpRequestMock = $this->httpRequestProfecy->reveal();
        $httpResponseMock = $this->httpResponseProfecy->reveal();

        $this->httpClientProfecy->createRequest()->willReturn($httpRequestMock);
        $this->httpClientProfecy->send($httpRequestMock)->willReturn($httpResponseMock);

        $client = $this->container->get(UserClient::class);

        $this->assertTrue(true);
    }

    public function tearDown(): void
    {
        $this->mocker->checkPredictions();
    }
}

#[HttpClient("http://users")]
interface UserClient
{
    /**
     * @return User[]
     */
    #[Get]
    function getUsers(): array;
}

class User {
    public string $name;

    public int $age;
}