<?php

namespace Primavera\PersistenceTests\Repository;

use Primavera\Persistence\Annotation\Query;
use Primavera\Persistence\Annotation\Table;
use Primavera\Persistence\Repository\RepositoryInterface;
use Primavera\Persistence\Stereotype\Repository;
use Primavera\PersistenceTests\Entity\Users;

#[Repository(Users::class)]
#[Table('users')]
interface UsersRepository extends RepositoryInterface
{
    public function findOneByName($name);

    #[Query('WHERE type = :type')]
    public function findSingers($type): iterable;
}