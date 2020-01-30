<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

use function array_key_exists;
use function array_keys;
use function filter_var;
use function is_numeric;
use function is_string;
use function sort;

use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_DOMAIN;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;
use const FILTER_VALIDATE_MAC;
use const FILTER_VALIDATE_REGEXP;

class ArrayValidator implements ValidableInterface, KeyValueValidableInterface
{
    private array $schema = [];
    private array $keys = [];
    private bool $isValid = true;

    private array $validators = [];

    public function notEmpty(...$keys): self
    {
        $this->validators[] = static function (array $array) use ($keys) {
            return self::validateNotEmpty($array, $keys);
        };

        return $this;
    }

    public function keys(...$keys): self
    {
        $this->validators[] = static function (array $array) use ($keys) {
            return self::validateKeys($array, $keys);
        };

        return $this;
    }

    public function hasKeys(...$keys): self
    {
        $this->validators[] = static function (array $array) use ($keys) {
            return self::validateHasKeys($array, $keys);
        };

        return $this;
    }

    public function key($key): self
    {
        $this->validators[] = static function (array $array) use ($key) {
            return self::validateKey($array, $key);
        };

        return $this;
    }

    public function hasKey($key): self
    {
        $this->validators[] = static function (array $array) use ($key) {
            return self::validateHasKey($array, $key);
        };

        return $this;
    }

    public function uValidate($array, callable $fn): bool
    {
        return $this->validate($array) && $fn($array);
    }

    public function validate($array): bool
    {
        foreach ($this->validators as $validatorFn) {
            $this->isValid = $this->isValid && $validatorFn($array);
        }

        return $this->isValid;
    }

    public function schema(array $schema): self
    {
        $this->schema = $schema;
        $this->keys = array_keys($schema);

        $this->validators[] = static function (array $array) use ($schema) {
            return self::validateSchema($array, $schema);
        };

        return $this;
    }

    public static function validateSchema($array, $schema): bool
    {
        $isValid = true;
        foreach ($schema as $key => $filter) {
            if ($filter === 'boolean' && filter_var($array[$key], FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            if ($filter === 'domain' && filter_var($array[$key], FILTER_VALIDATE_DOMAIN)) {
                continue;
            }

            if ($filter === 'int' && filter_var($array[$key], FILTER_VALIDATE_INT)) {
                continue;
            }

            if ($filter === 'email' && filter_var($array[$key], FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($filter === 'mac' && filter_var($array[$key], FILTER_VALIDATE_MAC)) {
                continue;
            }

            if ($filter === 'float' && filter_var($array[$key], FILTER_VALIDATE_FLOAT)) {
                continue;
            }

            if ($filter === 'regexp' && filter_var($array[$key], FILTER_VALIDATE_REGEXP)) {
                continue;
            }

            if ($filter === 'string' && is_string($array[$key])) {
                continue;
            }

            if ($filter === 'numeric' && is_numeric($array[$key])) {
                continue;
            }

            $isValid = false;
        }
        return $isValid;
    }

    public static function validateNotEmpty($array, $keys): bool
    {
        foreach ($keys as $key) {
            if (empty($array[$key])) {
                return false;
            }
        }
        return true;
    }

    public static function validateHasKeys($array, $keys): bool
    {
        foreach ($keys as $keyToCheck) {
            if (!array_key_exists($keyToCheck, $array)) {
                return false;
            }
        }
        return true;
    }

    public static function validateHasKey($array, $key): bool
    {
        return array_key_exists($key, $array);
    }

    public static function validateKey($array, $key): bool
    {
        return self::validateHasKey($array, $key);
    }

    public static function validateKeys($array, $keys): bool
    {
        $keysToValidate = array_keys($array);

        sort($keys);
        sort($keysToValidate);

        return $keys === $keysToValidate;
    }
}
