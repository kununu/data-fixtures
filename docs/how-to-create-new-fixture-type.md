# How to create a new Fixture Type
-----------------

This package already provides implementations to load fixtures for some storage types. You can find the list of all supported storage types [here](/README.md/Fixtures-types).
Still, if you have the need to create a new type you can do it and it's pretty simple.
For example, let's imagine that you need to load fixtures(in this case files) to a specific directory. To get this new type of fixtures up and running you will need to create a set of elements:
- [Fixture Interface](#Create-fixture-type-interface)
- [Purger](#Create-Purger)
- [Loader](#Create-Loader)
- [Executor](#Create-Executor)
- [Concrete Fixtures](#Create-Fixtures)

## Create fixture type interface

Any fixture that you create will need to implement the [FixtureInterface](/src/FixtureInterface.php) provided by this package.
In this example we will create the *DirectoryFilesFixtureInterface*, which exposes a method called *load* that will receive the directory name on which the fixtures should be loaded. It's then up to your concrete fixtures to save the files in the directory. We will create those concrete fixtures later.

```php
<?php

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\FixtureInterface;

interface DirectoryFilesFixtureInterface extends FixtureInterface
{
    public function load(string $dirnname): void;
}
```

## Create Purger

A purger is a class responsible for clearing the contents of a data storage.
In order to create a new Purger you need to implement the [PurgerInterface](/src/Purger/PurgerInterface.php).
In this example, the Purger will be responsible for removing all files in a specific directory.


```php
<?php

namespace Kununu\DataFixtures\Purger;

final class DirectoryPurger implements PurgerInterface
{
    private $dirname;

    public function __construct(string $dirname)
    {
        $this->dirname = $dirname;
    }

    public function purge(): void
    {
        $files = glob(sprintf('%s/*', $this->dirname));

        foreach($files as $file){
            if(is_file($file)) {
                unlink($file);
            }
        }
    }
}

```

## Create Loader

A loader is a class responsible for loading data fixtures of a specific type in multiple ways. In order to ease the creating of a loader this package already provides a default [loader](/src/Loader/Loader.php) which only requires you to define which types of fixtures it supports. In this example we will create the *DirectoryFixturesLoader* which extends the *default* loader.

```php
<?php

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\DirectoryFilesFixtureInterface;

final class DirectoryFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(DirectoryFilesFixtureInterface::class, class_implements($className));
    }
}
```

## Create Executor

A Executor is a class responsible of orchestrating the flow: calling the purger and loading the fixtures.
In order to create a new Executor you need to implement the [ExecutorInterface](/src/Executor/ExecutorInterface.php).
In this example, the Executor will be responsible for calling the Purger and load each fixture.

```php
<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\DirectoryFilesFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;

final class DirectoryExecutor implements ExecutorInterface
{
    private $dirname;
    private $purger;

    public function __construct(string $dirname, PurgerInterface $purger)
    {
        $this->dirname = $dirname;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false) : void
    {
        if ($append === false) {
            $this->purger->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($fixture);
        }
    }

    private function load(DirectoryFilesFixtureInterface $fixture)
    {
        $fixture->load($this->dirname);
    }
}

```

## Create Fixtures

Now that we created all the pieces required to load fixtures into a directory it's time to create the concrete fixtures.
In this example we will create two fixtures classes that will save files to a directory.

```php
<?php

namespace Kununu\DataFixtures;

use Kununu\DataFixtures\Adapter\DirectoryFilesFixtureInterface;

final class DirectoryFixture1 implements DirectoryFilesFixtureInterface
{
    public function load(string $dirnname): void
    {
        $fileName = sprintf('%s/file_1.txt', $dirnname);

        if (file_exists($fileName)) {
            $file = file_get_contents($fileName);
        } else {
            $file = '';
        }

        $file .= "João Alves\n";
        file_put_contents($fileName, $file);
    }
}

```

```php
<?php

namespace Kununu\DataFixtures\Purger;

use Kununu\DataFixtures\Adapter\DirectoryFilesFixtureInterface;

final class DirectoryFixture2 implements DirectoryFilesFixtureInterface
{
    public function load(string $dirnname): void
    {
        $fileName = sprintf('%s/file_2.txt', $dirnname);

        if (file_exists($fileName)) {
            $file = file_get_contents($fileName);
        } else {
            $file = '';
        }

        $file .= "Hugo Gonçalves\n";
        file_put_contents($fileName, $file);
    }
}
```


## Putting it all together

Now that you created your fixtures, the Purger, the Executor and the Loader it's time to put it all together:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$dirname = sprintf('%s/temp', __DIR__);

$purger = new \Kununu\DataFixtures\Purger\DirectoryPurger($dirname);

$executor = new Kununu\DataFixtures\Executor\DirectoryExecutor($dirname, $purger);

$loader = new Kununu\DataFixtures\Loader\DirectoryFixturesLoader();
$loader->addFixture(new \Kununu\DataFixtures\DirectoryFixture1());
$loader->addFixture(new \Kununu\DataFixtures\DirectoryFixture2());

$executor->execute($loader->getFixtures());

// If you want you can `append` the fixtures instead of purging the directory
$executor->execute($loader->getFixtures(), true);
```