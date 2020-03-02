<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

class Meaning extends Item
{
    private string $meaning;
    private ?string $entryId;
    private ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getEntryId(): ?string
    {
        return $this->entryId;
    }

    public function setEntryId(?string $entryId)
    {
        $this->entryId = $entryId;
    }

    public function __construct($meaning)
    {
        $this->meaning = $meaning;
    }

    public function getMeaning(): string
    {
        return $this->meaning;
    }

    public function setMeaning(string $meaning)
    {
        $this->meaning = $meaning;
    }
}
