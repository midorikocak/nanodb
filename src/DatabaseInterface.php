<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use PDO;

interface DatabaseInterface
{
    public function __construct(PDO $db);

    public function select($table, array $columns = ['*']): self;

    public function delete($table): self;

    public function update($table, array $values): self;

    public function insert($table, array $values): self;

    public function where($key, $value): self;

    public function and($key, $value): self;

    public function or($key, $value): self;

    public function between($key, $before, $after): self;

    public function lastInsertId(): string;

    public function rowCount(): int;

    public function execute(): bool;

    public function fetch(): array;

    public function fetchAll(): array;
}
