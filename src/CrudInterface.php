<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

interface CrudInterface
{
    public function read(string $id);

    public function readAll(array $constraints = [], array $columns = []): array;

    public function save($item);

    public function remove($data): int;
}
