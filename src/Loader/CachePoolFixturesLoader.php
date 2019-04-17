<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;

final class CachePoolFixturesLoader extends Loader
{
    protected function supports(string $className) : bool
    {
        return in_array(CachePoolFixtureInterface::class, class_implements($className)) ? true : false;
    }
}
