<?php

namespace Primavera\PersistenceTests\Framework;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Primavera\Cache\Factory;
use Primavera\Container\Annotation\Autowired;
use Primavera\Framework\Application;
use Primavera\Framework\Exception\HttpNotFoundException;
use Primavera\Framework\Stereotype\Controller;
use Primavera\Framework\Test\TestCase;
use Primavera\Http\Stereotype\Delete;
use Primavera\Http\Stereotype\Get;
use Primavera\Http\Stereotype\Post;
use Primavera\Http\Stereotype\Put;
use Primavera\Http\Stereotype\RequestBody;
use Primavera\Persistence\Annotation\Table;
use Primavera\Persistence\Persister\PersisterInterface;
use Primavera\Persistence\Stereotype\Persister;
use Primavera\PersistenceTests\Entity\Users;
use Primavera\PersistenceTests\Repository\UsersRepository;

class FrameworkTest extends TestCase
{
    #[Autowired]
    private ?Connection $connection;

    private $data = [
        'bruce' => ['name' => 'Bruce Dickinson', 'email' => 'brune@email.com', 'type' => 'singer'],
        'lars' => ['name' => 'Lars Ulrich', 'email' => 'lars@email.com', 'type' => 'drummer'],
        'dave' => ['name' => 'Dave Grohl', 'email' => 'dave@email.com', 'type' => 'singer'],
    ];

    private $addedData = [
        'mathews' => ['name' => 'Dave Mathews', 'email' => 'mathews@email.com', 'type' => 'singer'],
    ];

    public function setupApplication(Application $application) 
    {
        $application->addNamespaces('Primavera\PersistenceTests\\')
            ->withAppConfigFile(__DIR__ . '/../application.yaml')
            ->getBuilder()
            ->withCache(
                (new Factory)
                    ->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, '', 0, 'build/cache')
            )
        ;
    }

    public function setUp(): void
    {
        parent::setUp();

        $connection  = $this->connection;

        $schema = new Schema();
        $userTable = $schema->createTable('users');
        $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $userTable->addColumn('name', 'string', ['length' => 256]);
        $userTable->addColumn('email', 'string', ['length' => 256]);
        $userTable->addColumn('type', 'string', ['length' => 256]);
        $userTable->setPrimaryKey(['id']);

        $connection->executeQuery(implode(';', $schema->toSql($connection->getDatabasePlatform())));

        $connection->insert('users', $this->data['bruce']);
        $this->data['bruce']['id'] = (int) $connection->lastInsertId();
        $connection->insert('users', $this->data['lars']);
        $this->data['lars']['id'] = (int) $connection->lastInsertId();
        $connection->insert('users', $this->data['dave']);
        $this->data['dave']['id'] = (int) $connection->lastInsertId();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        
        $this->connection->close();
        $this->connection = null;
        unlink('fr_test_database.db');
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

        $this->assertEquals([...$this->addedData['mathews'], ...['id' => 4]], json_decode($data->getBody()->getContents(), true));
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
        private UsersRepository $usersRepository,
        private UsersPersister $usersPersister,
    ) {}

    #[Get]
    public function list() 
    {
        return $this->usersRepository->find();
    }

    #[Get('/{id}')]
    public function get($id) 
    {
        $value = $this->usersRepository->findById($id);

        if (!$value) {
            throw new HttpNotFoundException();
        }

        return $value;
    }

    #[Post]
    public function post(Users $data) 
    {
        return $this->usersPersister->save($data);
    }

    #[Put('{id}')]
    public function put(int $id, #[RequestBody] Users $data) 
    {
        $data->id = $id;

        return $this->usersPersister->save($data);
    }

    #[Delete]
    public function delete(int $id) 
    {
        return $this->usersPersister->delete($id);
    }

    #[Put('{id}/param')]
    public function paramRequestBody($id, #[RequestBody] Users $foo) 
    {
        return [$id, $foo];
    }
}

/**
 * @extends PersisterInterface<Users>
 */
#[Persister(Users::class)]
#[Table('users', autoIncrementId: true)]
interface UsersPersister extends PersisterInterface
{

}