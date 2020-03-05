<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use Exception;
use midorikocak\querymaker\QueryInterface;
use ReflectionException;

use function array_filter;
use function array_key_exists;
use function array_map;
use function is_array;
use function lcfirst;
use function preg_replace;
use function str_replace;
use function strtolower;
use function ucwords;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $primaryKey = '';
    protected array $foreignKeys = [];
    protected string $tableName = '';
    protected string $className = '';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @throws ReflectionException
     */
    public function read(string $id): Item
    {
        if ($id) {
            $this->db->select($this->tableName)->where('id', $id)->execute();
        } else {
            $this->db->select($this->tableName)->execute();
        }
        return $this->className::fromArray($this->db->fetch());
    }

    public function readResultSet(QueryInterface $query): array
    {
        return ResultSet::getResultArray($this->db, $query);
    }

    /**
     * @return Item[]
     */
    public function readAll(?QueryInterface $query = null): array
    {
        if ($query !== null) {
            $db = $this->db->query($query);
        } else {
            $db = $this->db->select($this->tableName);
        }

        $db->execute();
        $items = $db->fetchAll();
        return array_map(function ($item) {
            $object = $this->className::fromArray($item);
            if (array_key_exists($this->primaryKey, $item)) {
                $object->{self::getSetter($this->primaryKey)}($item[$this->primaryKey]);
            }

            foreach ($this->foreignKeys as $key) {
                if (array_key_exists($key, $item)) {
                    $object->{self::getSetter($key)}($item[$key]);
                }
            }

            return $object;
        }, $items);
    }

    /**
     * @param Item $item
     * @throws ReflectionException
     */
    public function save($item): Item
    {
        $itemData = array_filter($item->toArray(), fn($item) => !is_array($item) && $item);

        if ($item->getId() !== null) {
            $id = $itemData['id'];

            unset($itemData['id']);

            $this->db->update($this->tableName, $itemData)->where('id', $id)->execute();

            return $this->read($id);
        }

        if ($this->db->insert($this->tableName, $itemData)->execute() === false) {
            throw new Exception('Not Found.');
        }

        $lastInsertId = $this->db->lastInsertId();
        $updatedItem = $this->db->select($this->tableName)->where('id', $lastInsertId)->fetch();

        $item = $this->className::fromArray($updatedItem);
        $item->setFromArray($updatedItem);
        return $item;
    }

    public function remove($item): int
    {
        $id = $item->getId();
        if ($id !== null) {
            $this->db->delete($this->tableName)->where('id', $id)->execute();
            return $this->db->rowCount();
        }
        return 0;
    }

    private static function getSetter(string $arrayKey): string
    {
        return 'set' . lcfirst(self::makeCamel($arrayKey));
    }

    private static function makeKebab($camel): string
    {
        return strtolower(preg_replace('%([A-Z])([a-z])%', '_\1\2', $camel));
    }

    private static function makeCamel($kebab, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('-', '', ucwords($kebab, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}
