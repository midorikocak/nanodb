![nano API](nano.png)
# Nano DB

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


Nano DB is a tiny php library that allows you to define easily usable repositories.

There are 3 handy classes and 1 example in this library.
Let's start with basics:

## Requirements

Strictly requires PHP 7.4.

## Install

Via Composer

``` bash
$ composer require midorikocak/nanodb
```

## Usage 

### Database

To use database library, simple inject it with pdo.
    
```php
    use midorikocak\nanodb\Database;

    $pdo = new PDO('sqlite::memory:');
    $db = new Database($pdo);
``` 

Operations are chained.

```php
    $db->insert($tableName, $data)->execute();

    $lastInsertId = $db->lastInsertId();
    $insertedItem = $db->select($tableName)->where('id', $lastInsertId)->fetch();
```


### Select

If found, returns the data you need. If nothing found, empty array is returned.

```php
    print_r($db->select($tableName)->where('id', $id)->fetch());
``` 

Example output:

```
Array
(
    [id] => 1
    [username] => username
    [email] => email@email.com
    [password] => 123456789
)
``` 

### Insert

Insert using an array of data. Validation is your responsibility.

```php
    $db->select($tableName)->insert($tableName, $data)->executre();
``` 

### Update

Insert using an array of data. Again validation is your responsibility. If id does not exist, throws exception.

```php
   $db->update($tableName, $data)->update($tableName, $data)->where('id', $id)->execute();
``` 

### Delete

Returns affected rows. If id does not exist, throws exception.

```php
    $db->delete($tableName)->delete('id', $id)->execute();
``` 

## CrudInterface

The crud interface is the interface of repositories.

```php
<?php

declare(strict_types=1);

namespace midorikocak\nanodb;

interface CrudInterface
{
    public function read(string $id);

    public function readAll(array $constraints = [], array $columns = []): array;

    public function save($item);

    public function remove($data): int;
}

```

If you want to use arrays to interact with your database, you can use the array repository.

```php
use midorikocak\nanodb\ArrayRepository;


$tableName = 'users';
$schema = [
    'username'=>'string',
    'password'=>'string',
    'email'=>'email'
];


$repository = new ArrayRepository($tableName, $this->db, $schema);

```

Here `$schema` array is a simple optional array for Array validator, checked on every input with data. You can override it by extending `ArrayValidator` class.

```php
use midorikocak\nanodb\ArrayRepository;


$tableName = 'users';

$customValidator = new Validator();

$repository = new ArrayRepository($tableName, $this->db, null , $customValidator);

```

Validators can implement `ValidableInterface` and `KeyValueValidableInterface`. You can find details in the source.

### Class Repositories

Let's say you have a simple user class.

```php
<?php

declare(strict_types=1);

class User
{
    private ?string $id;
    private string $username;
    private string $email;
    private string $password;

    public function __construct(?string $id, string $username, string $email, string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }
}
```

You can create a `Users`repository, by implementing the `CrudInterface`.

```php
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

    /**
     * @return User
    */
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
     * @return User
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

    /**
     * @param User $user
     * @return User
    */
    public static function fromArray(array $array): User
    {
        if (!isset($array['id'])) {
            $array['id'] = null;
        }
        return new User($array['id'], $array['username'], $array['email'], $array['password']);
    }

    /**
     * @param User $user
     * @return array
    */
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

```

## Motivation and Warning

Mostly educational purposes. Please use at your own risk.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mtkocak@gmail.com instead of using the issue tracker.

## Credits

- [Midori Kocak][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/midorikocak/nanodb.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/midorikocak/nanodb/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/midorikocak/nanodb.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/midorikocak/nanodb.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/midorikocak/nanodb.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/midorikocak/nanodb
[link-travis]: https://travis-ci.org/midorikocak/nanodb
[link-scrutinizer]: https://scrutinizer-ci.com/g/midorikocak/nanodb/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/midorikocak/nanodb
[link-downloads]: https://packagist.org/packages/midorikocak/nanodb
[link-author]: https://github.com/midorikocak
[link-contributors]: ../../contributors
