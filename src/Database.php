<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use InvalidArgumentException;
use midorikocak\querymaker\QueryInterface;
use midorikocak\querymaker\QueryMaker;
use PDO;
use PDOStatement;

class Database implements DatabaseInterface
{
    private PDO $db;
    private ?QueryInterface $query = null;
    private ?PDOStatement $statement = null;

    public function __construct(PDO $db, ?QueryInterface $query = null)
    {
        $this->db = $db;
        $this->query = $query ?? new QueryMaker();
    }

    public function query(QueryInterface $query): self
    {
        $this->reset();
        $this->query = $query;
        return $this;
    }

    public function select($table, array $columns = ['*']): self
    {
        $this->reset();
        $this->query->select($table, $columns);
        return $this;
    }

    public function delete($table): self
    {
        $this->reset();
        $this->query->delete($table);
        return $this;
    }

    public function update($table, array $values): self
    {
        $this->reset();
        $this->query->update($table, $values);
        return $this;
    }

    public function insert($table, array $values): self
    {
        $this->reset();
        $this->query->insert($table, $values);
        return $this;
    }

    public function where($key, $value, string $operator = '='): self
    {
        $this->query->where($key, $value, $operator);
        return $this;
    }

    public function and($key, $value, string $operator = '='): self
    {
        $this->query->and($key, $value, $operator);
        return $this;
    }

    public function orderBy($key, $order = 'ASC'): self
    {
        if ($order !== 'DESC' && $order !== 'ASC') {
            throw new InvalidArgumentException('Invalid order value');
        }

        $this->query->orderBy($key, $order);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->query->offset($offset);
        return $this;
    }

    public function or($key, $value, string $operator = '='): self
    {
        $this->query->or($key, $value, $operator);
        return $this;
    }

    public function between($key, $before, $after): self
    {
        $this->query->between($key, $before, $after);
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
        $this->statement = $this->db->prepare($this->query->getStatement());
        return $this->statement->execute($this->query->getParams());
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

    private function reset()
    {
        $this->statement = null;
    }
}
