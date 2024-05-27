<?php

namespace Primavera\Doctrine\Test\Framework;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Primavera\Cache\Factory;
use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Factory\ContainerBuilder;
use Primavera\Doctrine\Test\Entity\Phone;
use Primavera\Doctrine\Test\Entity\User;
use Primavera\Doctrine\Test\Repository\UserRepository;
use Primavera\Framework\Application;
use Primavera\Framework\Component\Psr7Factory;
use Primavera\Framework\Exception\HttpNotFoundException;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Framework\Test\TestCase;
use Primavera\Http\Stereotype\Delete;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\RequestBody;

class ControllerTest extends TestCase
{
    #[Autowired]
    public ?EntityManager $em;

    private $data = [
        'bruce' => ['name' => 'Bruce Dickinson', 'email' => 'brune@email.com', 'type' => 'singer', 'phones' => [['phone' => '1111-1111']]],
        'lars' => ['name' => 'Lars Ulrich', 'email' => 'lars@email.com', 'type' => 'drummer', 'phones' => [['phone' => '2222-2222']]],
        'dave' => ['name' => 'Dave Grohl', 'email' => 'dave@email.com', 'type' => 'singer', 'phones' => [['phone' => '3333-3333']]],
    ];

    private $addedData = [
        'mathews' => ['name' => 'Dave Mathews', 'email' => 'mathews@email.com', 'type' => 'singer', 'phones' => [['phone' => '5555-5555']]],
    ];

    public function setUp(): void
    {
        parent::setUp();
        @unlink('dt_test_database.db');

        $connection  = $this->em->getConnection();

        $schema = new Schema();

        $userTable = $schema->createTable('users');
        $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $userTable->addColumn('name', 'string', ['length' => 256]);
        $userTable->addColumn('email', 'string', ['length' => 256]);
        $userTable->addColumn('type', 'string', ['length' => 256]);
        $userTable->setPrimaryKey(['id']);

        $phoneTable = $schema->createTable('phones');
        $phoneTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $phoneTable->addColumn('user_id', 'integer');
        $phoneTable->addColumn('phone', 'string', ['length' => 256]);
        $phoneTable->setPrimaryKey(['id']);
        $phoneTable->addForeignKeyConstraint('users', ['user_id'], ['id']);

        $commands = $schema->toSql($connection->getDatabasePlatform());

        foreach ($commands as $command) {
            $connection->executeQuery($command);
        }

        $bruce = new User('Bruce Dickinson', 'brune@email.com', 'singer');
        $lars = new User('Lars Ulrich', 'lars@email.com', 'drummer');
        $dave = new User('Dave Grohl', 'dave@email.com', 'singer');

        $bruce->getPhones()->add(new Phone($bruce, '1111-1111'));
        $lars->getPhones()->add(new Phone($lars, '2222-2222'));
        $dave->getPhones()->add(new Phone($dave, '3333-3333'));

        $this->em->persist($bruce);
        $this->em->persist($lars);
        $this->em->persist($dave);

        $this->em->flush();

        $this->data['bruce']['id'] = $bruce->id;
        $this->data['lars']['id'] = $lars->id;
        $this->data['dave']['id'] = $dave->id;
        $this->data['bruce']['phones'][0]['id'] = $bruce->getPhones()[0]->id;
        $this->data['lars']['phones'][0]['id'] = $lars->getPhones()[0]->id;
        $this->data['dave']['phones'][0]['id'] = $dave->getPhones()[0]->id;
    }

    public function setupApplication(Application $application) 
    {
        $application->addNamespaces('Primavera\Doctrine\Test\\')
            ->withAppConfigFile(__DIR__ . '/../application.yaml');
    }

    public function configureBuilder(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->withCache(
            (new Factory())
                ->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, '', 0, 'build/cache')
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        
        $this->em->getConnection()->close();
        $this->em->close();
        $this->em = null;
        unlink('dt_test_database.db');
    }

    public function testShouldGetList() 
    {
        $data = $this->get('/users');

        $this->assertEquals(array_values($this->data), json_decode($data->getBody()->getContents(), true));
    }

    public function testShouldGetOne() 
    {
        $data = $this->get('/users/1');

        $this->assertEquals($this->data['bruce'], json_decode($data->getBody()->getContents(), true));
    }

    public function testShouldPostOne() 
    {
        $data = $this->post('/users', $this->addedData['mathews']);

        $this->assertEquals(
            [
                ...$this->addedData['mathews'],
                'id' => 4,
                'phones' => [
                    [...$this->addedData['mathews']['phones'][0], 'id' => 4],

                ]
            ],
            json_decode($data->getBody()->getContents(), true)
        );
    }

    public function testShouldPutOne() 
    {
        $data = $this->put('/users/2', $putData = [...$this->data['lars'], 'type' => 'bad drummer']);

        $this->assertEquals($putData, json_decode($data->getBody()->getContents(), true));
    }

    public function testShouldDeleteOne() 
    {
        $data = $this->delete('/users/2');

        $this->assertStatus(204, $data);

        $this->assertNotFound($this->get('/users/2'));
    }
}


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
    public function post(User $data) 
    {
        $this->usersRepository->save($data);

        return $data;
    }

    #[Put('{id}')]
    public function put(int $id, #[RequestBody] User $data) 
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
