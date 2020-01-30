<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

interface ValidableInterface
{
    public function uValidate($toValidate, callable $fn): bool;

    public function validate($toValidate): bool;
}
