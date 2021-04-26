### Cache Pool Fixtures
-------------------------------

The `CachePoolFixtures` allows you to load data fixtures for any implementation of the [PSR-6](https://github.com/php-fig/cache) standard.

## Install

Before starting loading Cache Pool Fixtures make sure to add [PSR-6](https://github.com/php-fig/cache) as a dependency of your project.

```bash
composer require psr/cache
```

## How to load Cache Pool Fixtures?

### 1. Create fixture classes

The first step to load Cache Pool Fixtures is to create fixtures classes. This classes must implement the [CachePoolFixtureInterface](https://github.com/kununu/data-fixtures/blob/master/src/Adapter/CachePoolFixtureInterface.php) or if 

```php
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

### 2. Load fixtures

In order to load the fixtures that you created in the previous step, you will need to configure the CachePoolExecutor.

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
