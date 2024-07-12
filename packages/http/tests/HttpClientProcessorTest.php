<?php

namespace Primavera\Http\Tests;

use PHPUnit\Framework\TestCase;
use Primavera\Container\Container;
use Primavera\Container\ContainerBuilder;
use Primavera\Http\HttpClientInterface;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\HttpClient;
use Prophecy\Prophet;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientProcessorTest extends TestCase
{
    private Container $container;

    private ContainerBuilder $cb;

    private $httpClientProfecy;

    private $httpRequestProfecy;

    private $httpResponseProfecy;

    private HttpClientInterface $httpClientMock;

    private Prophet $mocker;

    public function setUp(): void
    {
        $this->mocker = $mocker = new Prophet();
        
        $this->cb = $cb = new ContainerBuilder();

        $cb->withNamespaces('Primavera\\Http\\Tests\\');

        $this->httpClientProfecy = $mocker->prophesize(HttpClientInterface::class);
        $this->httpRequestProfecy = $mocker->prophesize(RequestInterface::class);
        $this->httpResponseProfecy = $mocker->prophesize(ResponseInterface::class);
        $this->httpClientMock = $this->httpClientProfecy->reveal();

        $this->cb->withInstances([
            HttpClientInterface::class => $this->httpClientMock
        ]);

        $this->container = $this->cb->build();
    }

    public function testShouldImplemtnClient()
    {
        $this->httpResponseProfecy->getBody()->willReturn(json_encode([['name' => 'john', 'age' => 60]]));

        $httpRequestMock = $this->httpRequestProfecy->reveal();
        $httpResponseMock = $this->httpResponseProfecy->reveal();

        $this->httpClientProfecy->createRequest('GET', '/', [])->willReturn($httpRequestMock);
        $this->httpClientProfecy->send($httpRequestMock)->willReturn($httpResponseMock);

        $client = $this->container->get(UserClient::class);

        $this->assertEquals([new User('john', 60)], $client->getUsers());
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
    public function __construct(
        public string $name,
        public int $age,
    ) {}
}