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

abstract class AbstractRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $tableName = '';
    protected string $className = '';

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->className = __NAMESPACE__ . '\\' . $this->className;
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
            $article = $this->className::fromArray($item);
            if (array_key_exists('id', $item)) {
                $article->setId($item['id']);
            }
            if (array_key_exists('user_id', $item)) {
                $article->setUserId($item['user_id']);
            }

            return $article;
        }, $items);
    }

    /**
     * @param Item $item
     * @throws ReflectionException
     */
    public function save($item): Item
    {
        $itemData = array_filter($item->toArray(), fn($item) => !is_array($item));

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
}
