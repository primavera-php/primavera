<?php


namespace Vox\Framework\Tests\Controller;

use Primavera\Container\Annotation\Autowired;
use Vox\Framework\Stereotype\Controller;
use Vox\Framework\Stereotype\Delete;
use Vox\Framework\Stereotype\Get;
use Vox\Framework\Stereotype\Post;
use Vox\Framework\Stereotype\Put;
use Vox\Framework\Stereotype\RequestBody;
use Vox\Framework\Exception\HttpNotFoundException;


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
    public function post(FooDto $data) {
        return $this->service->post($data);
    }

    #[Put('{id}')]
    #[RequestBody('data')]
    public function put($id, FooDto $data) {
        return $this->service->put($id, $data);
    }

    #[Delete]
    public function delete($id) {
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
