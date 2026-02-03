<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;
use Kununu\DataFixtures\Loader\ElasticsearchFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticsearchFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticsearchFixture2;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class ElasticsearchFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new ElasticsearchFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return ElasticsearchFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 8;
    }

    protected function getFixtureClasses(): array
    {
        return [
            ElasticsearchFixture1::class => 'ElasticsearchFixture1.php',
            ElasticsearchFixture2::class => 'ElasticsearchFixture2.php',
        ];
    }
}
