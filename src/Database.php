<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\querymaker\QueryInterface;
use midorikocak\querymaker\QueryMaker;
use PDO;
use PDOStatement;

class Database implements DatabaseInterface
{
    private PDO $db;
    private ?QueryInterface $queryMaker = null;
    private ?int $affectedRows = 0;
    private ?PDOStatement $statement = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function select($table, array $columns = ['*']): self
    {
        $this->queryMaker = QueryMaker::select($table, $columns);
        return $this;
    }

    public function delete($table): self
    {
        $this->queryMaker = QueryMaker::delete($table);
        return $this;
    }

    public function update($table, array $values): self
    {
        $this->queryMaker = QueryMaker::update($table, $values);
        return $this;
    }

    public function insert($table, array $values): self
    {
        $this->queryMaker = QueryMaker::insert($table, $values);
        return $this;
    }

    public function where($key, $value): self
    {
        $this->queryMaker->where($key, $value);
        return $this;
    }

    public function and($key, $value): self
    {
        $this->queryMaker->and($key, $value);
        return $this;
    }

    public function or($key, $value): self
    {
        $this->queryMaker->or($key, $value);
        return $this;
    }

    public function between($key, $before, $after): self
    {
        $this->queryMaker->between($key, $before, $after);
        return $this;
    }

    public function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function execute(): bool
    {
        $this->statement = $this->db->prepare($this->queryMaker->getStatement());
        return $this->statement->execute($this->queryMaker->getParams());
    }

    public function fetch(): array
    {
        $this->execute();
        $toReturn = $this->statement->fetch(PDO::FETCH_ASSOC);
        return $toReturn === false ? [] : $toReturn;
    }

    public function fetchAll(): array
    {
        $this->execute();

        $toReturn = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        return $toReturn === false ? [] : $toReturn;
    }
}
