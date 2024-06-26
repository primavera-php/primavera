<?php


namespace Primavera\Framework\Test;


use Primavera\Container\Factory\ContainerBuilder;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Prophecy\Prophet;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Primavera\Framework\Application;
use Primavera\Http\HttpClient;
use Primavera\Http\HttpClientInterface;
use Primavera\Framework\Test\Http\HttpTestHandler;

class TestCase extends BaseTestCase implements HttpClientInterface
{
    protected ?Application $application = null;
    protected ?Prophet $prophet = null;
    protected ?HttpClient $http = null;
    protected ?HttpTestHandler $httpHandler = null;

    public function setApplication(Application $application): void
    {
        $this->application = $application;
        $this->http = new HttpClient($this->httpHandler = new HttpTestHandler($application));
    }
    
    public function setupApplication(Application $application) {
        // do nothing
    }

    public function configureBuilder(ContainerBuilder $containerBuilder) {
        // do nothing
    }

    public function setProphet(Prophet $prophet)
    {
        $this->prophet = $prophet;
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }

    public function delete(string $path, array $query = [], array $headers = []) {
        return $this->http->delete($path, $query, $headers);
    }

    public function get(string $path, array $query = [], array $headers = []) {
        return $this->http->get($path, $query, $headers);
    }

    public function post(string $path, $body, array $headers = []) {
        return $this->http->post($path, $body, $headers);
    }

    public function ignoreHttpErrors()
    {
        $this->httpHandler->ignoreErrors();

        return $this;
    }

    public function put(string $path, $body, array $headers = []) {
        return $this->http->put($path, $body, $headers);
    }

    public  function send(RequestInterface $request): ResponseInterface
    {
        return $this->http->send($request);
    }

    public function createRequest(string $method, string $path, array $headers = []): RequestInterface
    {
        return $this->http->createRequest($method, $path, $headers);
    }

    public function addBody(RequestInterface $request, string $body): RequestInterface
    {
        return $this->http->addBody($request, $body);
    }

    public function addQuery(RequestInterface $request, array $query): RequestInterface
    {
        return $this->http->addQuery($request, $query);
    }

    public function assertStatus(int $status, ResponseInterface $response) {
        $this->assertEquals($status, $response->getStatusCode());

        return $this;
    }

    public function assertOk(ResponseInterface $response) {
        $this->assertLessThan(300, $response->getStatusCode());
        $this->assertGreaterThanOrEqual(200, $response->getStatusCode());

        return $this;
    }

    public function assertNotFound(ResponseInterface $response) {
        $this->assertStatus(404, $response);

        return $this;
    }

    public function assertInternalError(ResponseInterface $response) {
        $this->assertGreaterThanOrEqual(500, $response->getStatusCode());

        return $this;
    }

    public function assertResponseContains(ResponseInterface $response, string $value) {
        $this->assertStringContainsString($value, $response->getBody()->getContents());

        return $this;
    }
}
