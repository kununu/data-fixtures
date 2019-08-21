<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;

final class ElasticSearchFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(ElasticSearchFixtureInterface::class, class_implements($className));
    }
}
