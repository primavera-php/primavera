<?php

namespace Vox\PersistenceTests\Repository;

use Vox\Persistence\Annotation\Query;
use Vox\Persistence\Annotation\Table;
use Vox\Persistence\Repository\RepositoryInterface;
use Vox\Persistence\Stereotype\Repository;
use Vox\PersistenceTests\Entity\Users;

#[Repository(Users::class)]
#[Table('users')]
interface UsersRepository extends RepositoryInterface
{
    public function findOneByName($name);

    #[Query('WHERE type = :type')]
    public function findSingers($type): iterable;
}