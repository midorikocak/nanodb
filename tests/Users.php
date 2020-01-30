<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use Exception;

use function array_map;
use function key;
use function reset;

class Users implements CrudInterface
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function read(string $id)
    {
        $data = $this->db->select('users')->where('id', $id)->fetch();
        if (!$data) {
            throw new Exception('not found');
        }
        return self::fromArray($data);
    }

    public function readAll(array $constraints = [], array $columns = ['*']): array
    {
        $db = $this->db->select('users', $columns);

        if (!empty($constraints)) {
            $value = reset($constraints);
            $key = key($constraints);
            $db->where($key, $value);

            unset($constraints[key($constraints)]);

            foreach ($constraints as $key => $value) {
                $db->and($key, $value);
            }
        }

        $db->execute();
        return array_map(fn($data) => self::fromArray($data), $db->fetchAll());
    }

    /**
     * @param User $user
     */
    public function save($user)
    {
        if ($user->getId()) {
            $id = $user->getId();
            $userData = self::toArray($user);
            unset($userData['id']);
            $this->db->update('users', $userData)->where('id', $id)->execute();
            return $user;
        }

        $this->db->insert('users', self::toArray($user))->execute();

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

    public static function fromArray($array): User
    {
        if (!isset($array['id'])) {
            $array['id'] = null;
        }
        return new User($array['id'], $array['username'], $array['email'], $array['password']);
    }

    public static function toArray(User $user): array
    {
        $toReturn = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ];

        if ($user->getId()) {
            $toReturn['id'] = $user->getId();
        }

        return $toReturn;
    }
}
