<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;

final class OpenSearchFixturesLoader extends Loader
{
    protected function supports(string $className): bool
    {
        return in_array(OpenSearchFixtureInterface::class, class_implements($className));
    }
}
