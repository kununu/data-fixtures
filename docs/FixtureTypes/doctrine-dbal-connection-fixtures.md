# Doctrine DBAL Connection Fixtures
-------------------------------

The `Doctrine DBAL Connection Fixtures` allows you to load data fixtures for any Connection configured with [Doctrine DBAL](https://github.com/doctrine/dbal).

## Install

Before starting loading Connection Fixtures make sure to add [Doctrine DBAL](https://github.com/doctrine/dbal) as a dependency of your project.

```bash
composer require doctrine/dbal
```

## How to load Connection Fixtures?

### 1. Create fixture classes

The first step to load *Connection Fixtures* is to create fixtures classes. This classes must implement the [ConnectionFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/Adapter/ConnectionFixtureInterface.php) or extend the class [ConnectionSqlFixture](https://github.com/kununu/data-fixtures/blob/master/src/Adapter/ConnectionSqlFixture.php) which allows you to define fixtures using *Sql*  files.


```php
use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class MyFixture implements ConnectionFixtureInterface
{
    public function load(Connection $connection): void
    {
        $connection->exec(
            'insert into `rules` (`id`, `block_id`, `field`, `operator`, `value`) values (1100, 1, "visits", "lower_than" , "5")'
        );
    }
}
```

```php
use Kununu\DataFixtures\Adapter\ConnectionSqlFixture;

final class MyFixtureSql extends ConnectionSqlFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Sql/pages.sql',
            __DIR__ . '/Sql/blocks.sql',
            __DIR__ . '/Sql/rules.sql',
        ];
    }
}
```

### 2. Load fixtures

In order to load the fixtures that you created in the previous step, you will need to configure the *Connection Executor*.

```php
$config = new Doctrine\DBAL\Configuration();

$connectionParams = [
    'url' => 'mysql://username:password@localhost/test_database'
];

$conn = Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$purger = new Kununu\DataFixtures\Purger\ConnectionPurger($conn);

$executor = new Kununu\DataFixtures\Executor\ConnectionExecutor($conn, $purger);

$loader = new Kununu\DataFixtures\Loader\ConnectionFixturesLoader();
$loader->addFixture(new MyFixtureSql());
$loader->addFixture(new MyFixture());

$executor->execute($loader->getFixtures());
```

### 3. Appending Fixtures

By default when loading fixtures the database is purged. If you want to change this behavior and instead append the fixtures, you can pass *false* as second argument to the ConnectionExecutor.

```php
...
$executor = new Kununu\DataFixtures\Executor\ConnectionExecutor($conn, $purger);

// If you want you can `append` the fixtures instead of purging the database
$executor->execute($loader->getFixtures(), true);
```

### 4. Exclude tables

When you do not append fixtures all tables from database are purged. Still, sometimes you may want to exclude some tables.
You can specify the tables being excluded from being purged by passing them as second argument to the Purger.

```php
...
$excludedTables = ['country_code', 'doctrine_migrations'];
$purger = new Kununu\DataFixtures\Purger\ConnectionPurger($conn, $excludedTables);

$executor = new Kununu\DataFixtures\Executor\ConnectionExecutor($conn, $purger);

$executor->execute($loader->getFixtures());
```

### 5. Purge mode

The Purger allows you to change the *Sql* statement used to purge the tables.
By default the Purger will run a *DELETE* statement to purge the tables but you can change it to use a *TRUNCATE* statement instead.

```php
...
$purger = new Kununu\DataFixtures\Purger\ConnectionPurger($conn, $excludedTables);

// If you want you can change the Purge Mode
$purger->setPurgeMode(1); // PURGE_MODE_DELETE
$purger->setPurgeMode(2); // PURGE_MODE_TRUNCATE

```

## Notes

- Connection Executor and Connection Purger are transactional.
- Connection Executor and Connection Purger disable foreign keys checks.