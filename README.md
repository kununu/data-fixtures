# kununu data-fixtures

[Doctrine data-fixtures](https://github.com/doctrine/data-fixtures) works great however it requires you to have a an ObjectManager in place, for example, by using the their [ORM](https://github.com/doctrine/orm).
In some kununu projects we only use the [Doctrine DBAL](https://github.com/doctrine/dbal) so that means that we are not able to use the Doctrine data-fixtures package.
Also, the need to have data fixtures for other storages, for example memcached, urged the need to build this package.

This library provides a simple way to manage and execute the loading of data fixtures for any storage mechanism. It's design and implementation was heavily based on the [Doctrine data-fixtures](https://github.com/doctrine/data-fixtures) package.

Currently, this package supports the following types of fixtures:

- `ConnectionFixture` which relies on Doctrine DBAL package by using it's [Connection](https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Connection.php) implementation.
- `CachePoolFixture` which relies on implementations of the [PSR6](https://github.com/php-fig/cache) standard.

If you are using Doctrine ORM and/or ODM use can still use https://github.com/doctrine/data-fixtures as you would normally do.

## Concepts

Each type of fixture relies on three major components:

- `Purger` - Class responsible of clearing the contents of a data storage
- `Loader` - Class responsible of loading data-fixtures of a specific type in multiple ways
- `Executor` - Class responsible of orchestrating the flow, by calling the purger and loading the fixtures

## Types of Fixtures

Bellow you cand a list of all supported types of fixtures.

### CachePoolFixtures

**1) Create your fixture classes that implement `CachePoolFixtureInterface`**

```
use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class MyFixture implements CachePoolFixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void
    {
        $item = $cachePool->getItem('a_key');
        $item->set('a_value');
        $cachePool->save($item);
    }
}
```

**2) Configure the `CachePoolExecutor` to load your fixtures**

```
$memcached = new \Memcached();
$memcached->addServer('localhost', 11211);

$cache = new Symfony\Component\Cache\Adapter\MemcachedAdapter($memcached);

$purger = new Kununu\DataFixtures\Purger\CachePoolPurger($cache);

$executor = new Kununu\DataFixtures\Executor\CachePoolExecutor($cache, $purger);

$loader = new Kununu\DataFixtures\Loader\CachePoolFixturesLoader();
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());
// If you want you can `append` the fixtures instead of purging the cache pool
$executor->execute($loader->getFixtures(), true);
```

### ConnectionFixture

**1) Create your fixture classes that implement `ConnectionFixtureInterface` or extend the class `ConnectionSqlFixture` which allows you to define fixtures using `SQL` files**

```
use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class MyFixture implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->exec(
            'insert into `rules`(`id`,`block_id`,`field`,`operator`,`value`) values (1100,1,"visits","lower_than","5")'
        );
    }
}
```

```
use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

final class MyFixtureSql extends ConnectionSqlFixture
{
    protected function filesName(): array
    {
        return [
            __DIR__ . '/Sql/pages.sql',
            __DIR__ . '/Sql/blocks.sql',
            __DIR__ . '/Sql/rules.sql',
        ];
    }
}
```

**2) Configure the `ConnectionExecutor` to load your fixtures**

```
$config = new Doctrine\DBAL\Configuration();

$connectionParams = [
    'url' => 'mysql://username:password@localhost/test_database'
];

$conn = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$purger = new Kununu\DataFixtures\Purger\ConnectionPurger($conn, $tables, $excludedTables);

// If you want you can change the Purge Mode
$purger->setPurgeMode(1); // PURGE_MODE_DELETE
$purger->setPurgeMode(2); // PURGE_MODE_TRUNCATE

$executor = new Kununu\DataFixtures\Executor\ConnectionExecutor($conn, $purger);

$loader = new Kununu\DataFixtures\Loader\ConnectionFixturesLoader();
$loader->addFixture(new MyFixtureSql());
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());
// If you want you can `append` the fixtures instead of purging the database
$executor->execute($loader->getFixtures(), true);
```

## Loading Fixtures

In order to load fixtures you have a couple of options available:

1) loadFromDirectory(string $dir)
2) loadFromFile(string $fileName)
3) addFixture(FixtureInterface $fixture)

```
$loader = new Kununu\DataFixtures\Loader\ConnectionFixturesLoader();
$loader->loadFromDirectory('/your/directory/');
$loader->loadFromFile('/your/file.php');
$loader->addFixture(new MyFixtureSql());
```

**Possible TODOs**

- Dependent Fixtures
- Fixture Group
- OrderedFixture

## Tests

Run the tests by doing:

```
composer install
vendor/phpunit/phpunit/phpunit tests
```
