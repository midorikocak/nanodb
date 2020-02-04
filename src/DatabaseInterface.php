<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\querymaker\QueryInterface;
use PDO;

interface DatabaseInterface
{
    public function __construct(PDO $db);

    public function select($table, array $columns = ['*']): self;

    public function delete($table): self;

    public function update($table, array $values): self;

    public function insert($table, array $values): self;

    public function where($key, $value, string $operator = '='): self;

    public function orderBy($key, string $order = 'ASC'): self;

    public function offset(int $offset): self;

    public function limit(int $limit): self;

    public function and($key, $value, string $operator = '='): self;

    public function or($key, $value, string $operator = '='): self;

    public function between($key, $before, $after): self;

    public function lastInsertId(): string;

    public function rowCount(): int;

    public function execute(): bool;

    public function fetch(): array;

    public function fetchAll(): array;

    public function query(QueryInterface $query);
}
