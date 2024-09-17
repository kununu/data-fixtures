# kununu data-fixtures

At kununu we rely on data fixtures in our tests as well in our development and testing environments.
A good definition of what fixtures are is the one from the documentation of [DoctrineFixturesBundle](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) in which the design and implementation of this package was heavily based on.

> Fixtures are used to load a “fake” set of data into a database that can then be used for testing or to help give you some interesting data while you’re developing your application.

### What is kununu/data-fixtures?

This package provides a simple way to manage and execute the loading of data fixtures for any storage mechanism. It's design and implementation was heavily based on the [Doctrine data-fixtures](https://github.com/doctrine/data-fixtures) package. If you are interested in why we created this package check out [Why kununu/data-fixtures?](docs/why-kununu-data-fixtures.md).

### Fixtures types

Currently, this package supports the following types of fixtures:

- *[Doctrine DBAL Connection Fixtures](docs/FixtureTypes/doctrine-dbal-connection-fixtures.md)* which relies on [Doctrine DBAL](https://github.com/doctrine/dbal) by using the [Connection](https://github.com/doctrine/dbal/blob/master/lib/Doctrine/DBAL/Connection.php) implementation
- *[Cache Pool Fixtures](docs/FixtureTypes/cache-pool-fixtures.md)* which relies on implementations of the [PSR-6](https://github.com/php-fig/cache) standard
- *[Elasticsearch Fixtures](docs/FixtureTypes/elasticsearch.md)* which relies on the [Elasticsearch-PHP client](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html)
- *[Symfony Http Client Fixtures](docs/FixtureTypes/symfony-http-client.md)* which relies on the [Symfony Http Client](https://github.com/symfony/http-client) and [Symfony Http Foundation](https://github.com/symfony/http-foundation).

Also check [Directory Loader](docs/FixtureTypes/directory-loader.md) to check how to load fixtures from files in a directory.

If you are interested in knowing more about the concepts of the package, or you need to create a new fixture type check out [How to create a new Fixture Type](docs/how-to-create-new-fixture-type.md).

--------------------------
## Install

#### 1. Add kununu/data-fixtures to your project

Before installing this package be aware:
- **You own the fixtures you load**
- **This package should not be used in production mode!**

```shell
composer require --dev kununu/data-fixtures
```

#### 2. Enable any fixture type

In order to enable the fixture types that you are interested, check out their documentation:

- [Doctrine DBAL Connection Fixtures](docs/FixtureTypes/doctrine-dbal-connection-fixtures.md)
- [Cache Pool Fixtures](docs/FixtureTypes/cache-pool-fixtures.md)
- [Elasticsearch Fixtures](docs/FixtureTypes/elasticsearch.md)
- [Symfony Http Client Fixtures](docs/FixtureTypes/symfony-http-client.md)

--------------------

## Append Fixtures

By default, when loading fixtures the data storage is purged. If you want to change this behavior and instead append the fixtures you can pass *false* as second argument to any executor.

```php
// By default, the data storage is purged
$executor->execute($loader->getFixtures());
// If you want you can `append` the fixtures instead of purging the database
$executor->execute($loader->getFixtures(), true);
```

--------------------

## Load Fixtures

In order to load fixtures the default [Loader](src/Loader/Loader.php) provides a couple of options:

1) `loadFromDirectory(string $dir)`
2) `loadFromFile(string $fileName)`
3) `loadFromClassName(string $className)`
4) `addFixture(FixtureInterface $fixture)`

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;

$loader = new ConnectionFixturesLoader();
$loader->loadFromDirectory('/your/directory/');
$loader->loadFromFile('/your/file.php');
$loader->loadFromClassName(MyFixtureSql::class);
$loader->addFixture(new MyFixtureSql());
```

------------------

## Initializable Fixtures

If you want your Fixture classes to be initialized you can implement the `InitializableFixtureInterface`

```php
public function initializeFixture(mixed ...$args): void;
```

Then before loading the fixtures you need to register them in the Loader:

```php
<?php
declare(strict_types=1);

use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;

$loader = new ConnectionFixturesLoader();

$this->loader->registerInitializableFixture(
	YourFixtureClass::class,
	// 1st argument
	1, 
	// 2nd argument
	'This is an argument that will be passed to initializeFixture of YourFixtureClass',
	// 3rd argument
	[
		'field'    => 'field-name',
		'value' => 10,
	],
	// 4th argument
	$anInstanceOfOneOfYourOtherClasses
	// Pass as many arguments as you like...
);

$loader->addFixture(new YourFixtureClass());
```

------------------------------

## Contribute

If you are interested in contributing read our [contributing guidelines](CONTRIBUTING.md).

------------------------------

## Tests

If not yet, first install composer dependencies:

```shell
composer install
```

Run the tests by doing: 

```shell
vendor/bin/phpunit
```

To run tests without coverage report:
```shell
composer install
composer test
```

To run tests with coverage report:
```shell
composer install
composer test-coverage
```

------------------------------

![Continuous Integration](https://github.com/kununu/data-fixtures/actions/workflows/continuous-integration.yml/badge.svg)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=kununu_data-fixtures&metric=alert_status)](https://sonarcloud.io/dashboard?id=kununu_data-fixtures)

