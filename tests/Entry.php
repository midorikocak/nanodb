<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

class Entry extends Item
{
    public ?string $id = null;

    public ?string $userId = null;

    private string $title;

    /** @var Meaning[] */
    protected array $meanings = [];

    public function __construct(string $title, ?string $id = null)
    {
        $this->title = $title;
        $this->id = $id;
    }

    public function addMeaning(Meaning $meaning)
    {
        $meaning->setEntryId($this->getId());
        $this->meanings[] = $meaning;
    }

    public function removeMeaning(Meaning $meaning)
    {
        $meaning->setEntryId(null);

        foreach ($this->meanings as $key => $value) {
            if ($value->getId() === $meaning->getId()) {
                unset($this->meanings[$value->getId()]);
                break;
            }
        }
    }

    public function getMeanings(): array
    {
        return $this->meanings;
    }

    /**
     * @param Meaning[] $meanings
     */
    public function setMeanings(array $meanings): void
    {
        $this->meanings = $meanings;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId)
    {
        $this->userId = $userId;
    }
}
