<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;

final class ConnectionFixturesLoader extends Loader
{
    protected function supports(string $className) : bool
    {
        return in_array(ConnectionFixtureInterface::class, class_implements($className)) ? true : false;
    }
}
