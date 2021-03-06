<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\querymaker\QueryMaker;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class AbstractRepositoryTest extends TestCase
{
    private DatabaseInterface $db;

    private PDO $pdo;

    private Dictionary $dictionary;

    public function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->db = new Database($this->pdo, new QueryMaker());
        $this->createTable();

        $this->dictionary = new Dictionary($this->db);
    }

    private function createTable(): void
    {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Error Handling
            $sql = "CREATE table entries(
     id INTEGER PRIMARY KEY AUTOINCREMENT,
     title TEXT NOT NULL UNIQUE,
     user_id INTEGER);

     CREATE table meanings(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        meaning TEXT,
        entry_id INTEGER
     );
     ";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage(); //Remove or change message in production code
        }
    }

    public function tearDown(): void
    {
        unset($this->pdo, $this->db);
    }

    public function testNewEntry(): void
    {
        $entry = new Entry('Object');

        $entry->addMeaning(new Meaning('A thing with a name'));
        $entry->addMeaning(new Meaning('Operated by subject'));

        $this->dictionary->save($entry);

        $entry = new Entry('Other Object');

        $entry->addMeaning(new Meaning('A thing with another name'));
        $entry->addMeaning(new Meaning('Operated by other subject'));

        self::assertNotEmpty($this->dictionary->save($entry));
    }

    public function testRead(): void
    {
        $entry = new Entry('Object');

        $newEntry = $this->dictionary->save($entry);

        $newEntry->addMeaning(new Meaning('A thing with a name'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));

        $this->dictionary->save($newEntry);
        $results = $this->dictionary->read($newEntry->getId());
        $this->assertNotEmpty($results);
    }

    /**
     * @throws ReflectionException
     */
    public function testReadAll(): void
    {
        $entry = new Entry('Object');

        $newEntry = $this->dictionary->save($entry);

        $newEntry->addMeaning(new Meaning('A thing with a name'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));
        $newEntry->addMeaning(new Meaning('Operated by subject'));

        $this->dictionary->save($newEntry);
        $results = $this->dictionary->readAll();
        $this->assertNotEmpty($results);

        $queryMaker = new QueryMaker();

        $query = $queryMaker->select('meanings')->limit(2)->offset(3);
        $resultArray = $this->dictionary->readResultSet($query);
        self::assertEquals(2, $resultArray['page']);
    }
}
