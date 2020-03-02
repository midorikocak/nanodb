<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use ReflectionException;

use function array_map;

class Dictionary extends AbstractRepository
{
    protected string $tableName = 'entries';
    protected string $className = 'Entry';

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
                $this->db->insert('meanings', $meaning->toArray())->execute();
                $meaning->setId($this->db->lastInsertId());
                $meaning->setEntryId($entry->getId());
            } else {
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

        $meaningsData = $this->db->select('meaings')->where('entry_id', $id)->fetchAll();
        $meanings = array_map(fn($data) => Meaning::fromArray($data), $meaningsData);

        $entry->setMeanings($meanings);

        return $entry;
    }
}
