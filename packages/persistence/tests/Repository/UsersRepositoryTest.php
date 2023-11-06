<?php

namespace Primavera\PersistenceTests\Repository;

use Doctrine\DBAL\Connection;
use Primavera\Container\Container\Container;
use Primavera\Container\Factory\ContainerBuilder;
use Primavera\Data\ObjectHydrator;
use Primavera\Persistence\Stereotype\Repository;
use Primavera\PersistenceTests\DbTestCase;
use Primavera\PersistenceTests\Entity\Users;

class UsersRepositoryTest extends DbTestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection->insert('users', ['name' => 'Bruce Dickinson', 'email' => 'brune@email.com', 'type' => 'singer']);
        $this->connection->insert('users', ['name' => 'Lars Ulrich', 'email' => 'lars@email.com', 'type' => 'drummer']);
        $this->connection->insert('users', ['name' => 'Dave Grohl', 'email' => 'dave@email.com', 'type' => 'singer']);

        $builder = new ContainerBuilder(true);

        $builder->withAppNamespaces()
            ->withNamespaces('Primavera\\PersistenceTests\\')
            ->withStereotypes(Repository::class)
            ->withConfigFile(__DIR__ . '/../application.yaml')
            ->withComponents(ObjectHydrator::class)
        ;

        $this->container = $builder->build();

    }

    public function testShouldGetRepositoryAndFindData()
    {
        /* @var $repo \Primavera\PersistenceTests\Repository\UsersRepository */
        $repo = $this->container->get(UsersRepository::class);

        $bruce = $repo->findById(1);
        $lars = $repo->findOneByName('Lars Ulrich');
        $singers = iterator_to_array($repo->findSingers('singer'));

        $this->assertInstanceOf(Users::class, $bruce);
        $this->assertEquals('Bruce Dickinson', $bruce->name);
        $this->assertEquals('lars@email.com', $lars->email);
        $this->assertCount(2, $singers);
        $this->assertEquals('Bruce Dickinson', $singers[0]->name);
        $this->assertEquals('Dave Grohl', $singers[1]->name);
    }

    protected function tearDown(): void
    {
        $this->container->get(Connection::class)->close();

        parent::tearDown();
    }

}