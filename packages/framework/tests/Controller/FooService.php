<?php


namespace Primavera\Framework\Tests\Controller;

use Primavera\Framework\Stereotype\Service;

#[Service]
class FooService
{
    private array $data;

    public function __construct()
    {
        $this->data = [
            new FooDto('bar'),
            new FooDto('baz'),
        ];
    }

    public function list() {
        return $this->data;
    }

    public function get($id) {
        return $this->data[$id] ?? null;
    }

    public function post(FooDto $data) {
        return $this->data[] = $data;
    }

    public function put($id, FooDto $data) {
        return $this->data[$id] = $data;
    }

    public function delete($id) {
        $this->data = array_filter($this->data, fn($index) => $id == $index, ARRAY_FILTER_USE_KEY);
    }
}
