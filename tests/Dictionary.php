<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use ReflectionException;

use function array_map;

class Dictionary extends AbstractRepository
{
    protected string $tableName = 'entries';
    protected string $className = __NAMESPACE__ . '\\' . 'Entry';

    /**
     * @param Entry $entry
     * @throws ReflectionException
     */
    public function save($entry): Entry
    {
        $newEntry = parent::save($entry);
        $entry->setId($newEntry->getId());
        $meanings = $entry->getMeanings();

        foreach ($meanings as &$meaning) {
            if ($meaning->getId() === null) {
                $meaning->setEntryId($newEntry->getId());
                $this->db->insert('meanings', $meaning->toArray())->execute();
                $meaning->setId($this->db->lastInsertId());
            } else {
                $meaning->setEntryId($entry->getId());
                $this->db->update('meanings', $meaning->toArray());
            }
        }
        return $entry;
    }

    public function read(string $id): Item
    {
        /**
         * @var Entry $entry
         */
        $entry = parent::read($id);

        $meaningsData = $this->db->select('meanings')->where('entry_id', $id)->fetchAll();

        $meanings = array_map(function ($data) {
            return Meaning::fromArray($data);
        }, $meaningsData);
        $entry->setMeanings($meanings);

        return $entry;
    }
}
