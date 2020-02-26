<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\querymaker\QueryMaker;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private Database $db;
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
        unset($this->pdo);
        unset($this->db);
    }

    public function testSelect(): void
    {
        $firstItem = $this->db->select('users')->fetch();
        $this->firstUser['id'] = 1;

        $this->assertEquals($this->firstUser, $firstItem);
    }

    public function testOrder(): void
    {
        $firstItem =
            $this->db
                ->select('users')
                ->orderBy('id')
                ->limit(1)
                ->offset(0)
                ->fetch();
        $this->firstUser['id'] = 1;

        $this->assertEquals($this->firstUser, $firstItem);
    }

    public function testSelectAll(): void
    {
        $allItems = $this->db->select('users')->fetchAll();
        $this->firstUser['id'] = 1;
        $this->secondUser['id'] = 2;

        $expectedItems = [$this->firstUser, $this->secondUser];

        $this->assertEquals($expectedItems, $allItems);
    }

    public function testUpdate(): void
    {
        $this->db
            ->update(
                'users',
                [
                    'username' => 'updated',
                    'email' => 'newemail@email.com',
                ]
            )->where('id', 1)->execute();

        $this->assertEquals(1, $this->db->rowCount());

        $firstItem = $this->db->select('users')->where('id', 1)->fetch();

        $this->firstUser['id'] = 1;
        $this->firstUser['username'] = 'updated';
        $this->firstUser['email'] = 'newemail@email.com';

        $this->assertEquals($this->firstUser, $firstItem);
    }

    public function testInsert(): void
    {
        $this->db
            ->insert(
                'users',
                [
                    'username' => 'inserted',
                    'password' => 'somepassword',
                    'email' => 'insert@email.com',
                ]
            )->execute();

        $lastInsertId = $this->db->lastInsertId();
        $insertedItem = $this->db->select('users')->where('id', $lastInsertId)->fetch();

        $lastInsertUser = [];
        $lastInsertUser['id'] = $lastInsertId;
        $lastInsertUser['username'] = 'inserted';
        $lastInsertUser['password'] = 'somepassword';
        $lastInsertUser['email'] = 'insert@email.com';

        $this->assertEquals($lastInsertUser, $insertedItem);
    }

    public function testDeleteCount(): void
    {
        $this->db->delete('users')->execute();
        $this->assertEquals(2, $this->db->rowCount());
    }

    public function testDelete(): void
    {
        $firstItem = $this->db->select('users')->fetch();
        $this->firstUser['id'] = 1;

        $this->assertEquals($this->firstUser, $firstItem);
    }
}
