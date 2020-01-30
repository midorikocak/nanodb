<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

interface KeyValueValidableInterface
{
    public function notEmpty(...$keys): self;

    public function keys(...$keys): self;

    public function hasKeys(...$keys): self;

    public function hasKey($key): self;

    public function schema(array $schema): self;
}
