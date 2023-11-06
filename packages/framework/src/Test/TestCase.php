<?php


namespace Primavera\Framework\Test;


use Primavera\Container\Factory\ContainerBuilder;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Prophecy\Prophet;
use Psr\Http\Message\ResponseInterface;
use Primavera\Framework\Application;
use Primavera\Framework\Component\Http\HttpClient;
use Primavera\Framework\Component\Http\HttpClientInterface;
use Primavera\Framework\Test\Http\HttpTestHandler;

class TestCase extends BaseTestCase implements HttpClientInterface
{
    protected ?Application $application = null;
    protected ?Prophet $prophet = null;
    protected ?HttpClient $http = null;

    public function setApplication(Application $application): void
    {
        $this->application = $application;
        $this->http = new HttpClient(new HttpTestHandler($application));
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

    public function put(string $path, $body, array $headers = []) {
        return $this->http->put($path, $body, $headers);
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
