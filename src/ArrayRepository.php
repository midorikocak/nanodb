<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use Exception;
use midorikocak\arraytools\ArrayValidator;

use function array_key_exists;
use function key;
use function reset;

/**
 * Repository is a class that receives arrays as input data and returns array of arrays
 */
class ArrayRepository implements RepositoryInterface
{
    private DatabaseInterface $db;
    private ?ArrayValidator $validator;
    private $tableName;

    public function __construct(
        string $tableName,
        DatabaseInterface $db,
        ?array $schema = null,
        ?ArrayValidator $validator = null
    ) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->validator = $validator ?? new ArrayValidator();
        if ($schema) {
            $this->validator->schema($schema);
        }
    }

    private function checkData($data): void
    {
        if (!$this->validator->validate($data)) {
            throw new Exception('Invalid data');
        }
    }

    /**
     * @inheritDoc
     */
    public function save($data): array
    {
        if (array_key_exists('id', $data)) {
            $this->checkData($data);

            $id = $data['id'];

            unset($data['id']);

            $this->db->update($this->tableName, $data)->where('id', $id)->execute();

            return $this->read($id);
        }

        $this->checkData($data);
        $this->db->insert($this->tableName, $data)->execute();

        $lastInsertId = $this->db->lastInsertId();
        return $this->db->select('users')->where('id', $lastInsertId)->fetch();
    }

    /**
     * @inheritDoc
     */
    public function read(?string $id = null): array
    {
        if ($id) {
            $this->db->select($this->tableName)->where('id', $id)->execute();
        } else {
            $this->db->select($this->tableName)->execute();
        }
        return $this->db->fetch();
    }

    public function readAll(
        array $filter = [],
        array $columns = ['*'],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $db = $this->db->select($this->tableName, $columns);

        if (!empty($filter)) {
            $value = reset($filter);
            $key = key($filter);
            $db->where($key, $value);

            unset($filter[key($filter)]);

            foreach ($filter as $key => $value) {
                $db->and($key, $value);
            }
        }

        if ($limit) {
            $this->db->limit($limit);
        }

        if ($limit && $offset) {
            $this->db->offset($offset);
        }

        $db->execute();
        return $db->fetchAll();
    }

    /**
     * @inheritDoc
     */
    public function remove($data): int
    {
        if (array_key_exists('id', $data)) {
            $this->db->delete($this->tableName)->where('id', $data['id'])->execute();
            return $this->db->rowCount();
        }
        return 0;
    }
}
