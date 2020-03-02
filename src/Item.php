<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use midorikocak\arraytools\ArrayConvertableTrait;
use midorikocak\arraytools\ArrayUpdateableTrait;

class Item
{
    use ArrayConvertableTrait;
    use ArrayUpdateableTrait;

    protected ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
