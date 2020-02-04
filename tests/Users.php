<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use Exception;
use midorikocak\querymaker\QueryInterface;

use function array_map;

class Users implements RepositoryInterface
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function read(string $id): User
    {
        $data = $this->db->select('users')->where('id', $id)->fetch();
        if (!$data) {
            throw new Exception('not found');
        }
        return User::fromArray($data);
    }

    public function readAll(?QueryInterface $query = null): array
    {
        if ($query !== null) {
            $db = $this->db->query($query);
        } else {
            $db = $this->db->select('users');
        }
        $db->execute();
        return array_map(fn($data) => User::fromArray($data), $db->fetchAll());
    }

    /**
     * @param User $user
     */
    public function save($user): User
    {
        if ($user->getId()) {
            $id = $user->getId();
            $userData = $user->toArray();
            unset($userData['id']);
            $this->db->update('users', $userData)->where('id', $id)->execute();
            return $user;
        }

        $this->db->insert('users', $user->toArray())->execute();

        $lastInsertId = $this->db->lastInsertId();
        $user->setId($lastInsertId);
        return $user;
    }

    /**
     * @param User $user
     */
    public function remove($user): int
    {
        $id = $user->getId();
        $this->db->delete('users')->where('id', $id)->execute();
        return $this->db->rowCount();
    }
}
