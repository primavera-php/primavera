<?php


namespace Primavera\Framework\Tests\Controller;

use Primavera\Container\Annotation\Autowired;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Http\Stereotype\Delete;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\RequestBody;
use Primavera\Framework\Exception\HttpNotFoundException;


class FooDto {
    public string $foo;

    public function __construct(string $foo) {
        $this->foo = $foo;
    }
}

#[Controller('/foo')]
class FooController
{
    #[Autowired]
    private FooService $service;

    #[Autowired]
    private MockableService $mockableService;

    #[Get('/mock')]
    public function getMockData() {
        return $this->mockableService->getMockData();
    }

    #[Get]
    public function list() {
        return $this->service->list();
    }

    #[Get('/{id}')]
    public function get($id) {
        $value = $this->service->get($id);

        if (!$value) {
            throw new HttpNotFoundException();
        }

        return $value;
    }

    #[Post]
    public function post(#[RequestBody] FooDto $data) {
        return $this->service->post($data);
    }

    #[Put('{id}')]
    #[RequestBody('data')]
    public function put(int $id, FooDto $data) {
        return $this->service->put($id, $data);
    }

    #[Delete]
    public function delete(int $id) {
        return $this->service->delete($id);
    }

    #[Post('error')]
    public function mappedError(#[RequestBody] $noType) {
        // should throw error, no type matched
    }

    #[Put('{id}/param')]
    public function paramRequestBody($id, #[RequestBody] FooDto $foo) {
        return [$id, $foo];
    }
}
