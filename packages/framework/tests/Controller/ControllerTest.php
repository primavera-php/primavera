<?php


namespace Primavera\Framework\Tests\Controller;

use Primavera\Container\Factory\ContainerBuilder;
use Primavera\Container\Annotation\Autowired;
use Prophecy\Prophecy\ObjectProphecy;
use Slim\Psr7\Factory\ServerRequestFactory;
use Primavera\Cache\Factory;
use Primavera\Framework\Application;
use Primavera\Framework\Test\Stereotype\Mock;
use Primavera\Framework\Test\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @var MockableService|ObjectProphecy
     */
    #[Mock(MockableService::class)]
    private ObjectProphecy $mockableService;
    
    public function setupApplication(Application $application) {
        $application->addNamespaces('Primavera\Framework\Tests\\');
    }

    public function configureBuilder(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->withCache(
            (new Factory())
                ->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, '', 0, 'build/cache')
        );
    }

    public function testShouldGetList() {
        $data = $this->get('/foo');

        $this->assertEquals('[{"foo":"bar"},{"foo":"baz"}]', $data->getBody()->getContents());
    }

    public function testShouldGetOne() {
        $data = $this->get('/foo/0');

        $this->assertEquals('{"foo":"bar"}', $data->getBody()->getContents());
    }

    public function testShouldPostOne() {
        $data = $this->post('/foo', ['foo' => 'bar baz']);

        $this->assertEquals('{"foo":"bar baz"}', $data->getBody()->getContents());
    }

    public function testShouldPutOne() {
        $data = $this->put('/foo/2', ['foo' => 'bar bazar']);

        $this->assertEquals('{"foo":"bar bazar"}', $data->getBody()->getContents());
    }

    public function testShouldDeleteOne() {
        $data = $this->delete('/foo/2', ['foo' => 'bar bazar']);

        $this->assertStatus(204, $data);

        $this->assertNotFound($this->get('/foo/2'));
    }

    public function testShouldMockData()
    {
        $this->mockableService->getMockData()->willReturn(['foo' => 'bar']);

        $data = $this->get('/foo/mock');

        $this->assertEquals('{"foo":"bar"}', $data->getBody()->getContents());
    }

    public function testShouldThrowErrorNoTypeDefined() {
        $response = $this->post('/foo/error', []);

        $this->assertInternalError($response)
            ->assertResponseContains($response, 'no type defined');
    }

    public function testShouldParseRequestParamOnParameter() {
        $data = $this->put('/foo/1/param', ['foo' => 'bar baz']);

        $this->assertEquals('["1",{"foo":"bar baz"}]', $data->getBody()->getContents());
    }
}