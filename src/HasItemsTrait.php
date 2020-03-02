<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

trait HasItemsTrait
{
    /** @var Item[] */
    protected array $items = [];

    public function addItem(Item $item)
    {
        $this->items[$item->getId()] = $item;
    }

    public function removeItem(Item $item)
    {
        unset($this->items[$item->getId()]);
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
