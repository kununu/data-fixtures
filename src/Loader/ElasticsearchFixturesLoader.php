<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;

final class ElasticsearchFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(ElasticsearchFixtureInterface::class, class_implements($className));
    }
}
