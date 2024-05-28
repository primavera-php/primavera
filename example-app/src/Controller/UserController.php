<?php

namespace Primavera\ExampleApp\Controller;

use Primavera\Doctrine\Annotation\Merge;
use Primavera\ExampleApp\Entity\User;
use Primavera\ExampleApp\Repository\UserRepository;
use Primavera\Framework\Component\Psr7Factory;
use Primavera\Framework\Exception\HttpNotFoundException;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Http\Stereotype\Delete;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\RequestBody;

#[Controller('users')]
class UserController
{
    public function __construct(
        private UserRepository $usersRepository,
        private Psr7Factory $psr7Factory,
    ) {}

    #[Get]
    public function list() 
    {
        return $this->usersRepository->findAll();
    }

    #[Get('/{id}')]
    public function get($id) 
    {
        $value = $this->usersRepository->findOneBy(['id' => $id]);

        if (!$value) {
            throw new HttpNotFoundException();
        }

        return $value;
    }

    #[Post]
    public function post(#[RequestBody] User $data) 
    {
        $this->usersRepository->save($data);

        return $data;
    }

    #[Put('{id}')]
    public function put(int $id, #[Merge] User $data) 
    {
        $data->id = $id;

        $this->usersRepository->save($data);

        return $data;
    }

    #[Delete]
    public function delete(int $id) 
    {
        $this->usersRepository->delete($id);

        return $this->psr7Factory->createResponse(204, "entity with id {$id} deleted");
    }
}
