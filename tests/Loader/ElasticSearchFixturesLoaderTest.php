<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchFixture2;

final class ElasticSearchFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new ElasticSearchFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return ElasticSearchFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 8;
    }

    protected function getFixtureClasses(): array
    {
        return [
            ElasticSearchFixture1::class => 'ElasticSearchFixture1.php',
            ElasticSearchFixture2::class => 'ElasticSearchFixture2.php',
        ];
    }
}
