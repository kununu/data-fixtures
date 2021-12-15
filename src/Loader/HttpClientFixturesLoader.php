<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;

final class HttpClientFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(HttpClientFixtureInterface::class, class_implements($className));
    }
}
