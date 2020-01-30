<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

use function count;

class ArrayRepositoryTest extends TestCase
{
    private DatabaseInterface $db;
    private ArrayRepository $repository;
    private PDO $pdo;

    private array $firstUser;
    private array $secondUser;

    public function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->db = new Database($this->pdo);
        $this->createTable();

        $this->firstUser = [
            'username' => 'midorikocak',
            'email' => 'mtkocak@gmail.com',
            'password' => '12345678',
        ];

        $this->secondUser = [
            'username' => 'newuser',
            'email' => 'email@email.com',
            'password' => '87654321',
        ];

        $this->insertUser($this->firstUser['email'], $this->firstUser['username'], $this->firstUser['password']);
        $this->insertUser($this->secondUser['email'], $this->secondUser['username'], $this->secondUser['password']);

        $schema = [
            'username' => 'string',
            'password' => 'string',
            'email' => 'email',
        ];

        $this->repository = new ArrayRepository('users', $this->db, $schema);
    }

    private function createTable(): void
    {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Error Handling
            $sql = "CREATE table users(
     id INTEGER PRIMARY KEY,
     username TEXT NOT NULL UNIQUE,
     email TEXT NOT NULL UNIQUE,
     password TEXT NOT NULL);";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage(); //Remove or change message in production code
        }
    }

    private function insertUser($email, $username, $password): void
    {
        $sql = "INSERT INTO users (email, username, password) VALUES (?,?,?)";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$email, $username, $password]);
    }

    public function tearDown(): void
    {
        unset($this->pdo, $this->db, $this->repository);
    }

    public function testUpdate(): void
    {
        $updatedData = [
            'username' => 'updateduser',
            'email' => 'updated@email.com',
            'password' => '12345update',
        ];

        $updatedData['id'] = $this->db->lastInsertId();
        $updated = $this->repository->save($updatedData);
        $this->assertEquals($updatedData, $updated);
    }

    public function testRemove(): void
    {
        $id = $this->db->lastInsertId();
        $this->repository->remove(['id' => $id]);

        $this->assertEmpty($this->repository->read($id));
    }

    public function testReadAll(): void
    {
        $read = $this->repository->readAll();
        $this->assertEquals(2, count($read));
    }

    public function testFindOne(): void
    {
        $read = $this->repository->read('1');

        $this->assertNotEmpty($read);
    }

    public function testCreate(): void
    {
        $insertData = [
            'username' => 'updateduser',
            'email' => 'updated@email.com',
            'password' => '12345update',
        ];

        $inserted = $this->repository->save($insertData);

        $insertData['id'] = $this->db->lastInsertId();

        $this->assertEquals($insertData, $inserted);
    }

    public function testRead(): void
    {
        $first = $this->repository->read('1');
        $this->firstUser['id'] = 1;
        $this->assertEquals($this->firstUser, $first);
    }
}
