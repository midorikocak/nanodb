<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

interface RepositoryInterface
{
    /**
     * @return array|object
     */
    public function read(string $id);

    /**
     * @return array[] | object[]
     */
    public function readAll(array $constraints = [], array $columns = []): array;

    /**
     * @param array|object $item if has id key or property, updates, else creates
     * @return array|object
     */
    public function save($item);

    /**
     * @param array|object $data should have id key or property
     */
    public function remove($data): int;
}
