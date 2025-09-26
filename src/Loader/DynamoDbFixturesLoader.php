<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\DynamoDbFixtureInterface;

final class DynamoDbFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(DynamoDbFixtureInterface::class, class_implements($className));
    }
}
