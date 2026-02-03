<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\DynamoDbFixtureInterface;
use Kununu\DataFixtures\Loader\DynamoDbFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\DynamoDbFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\DynamoDbFixture2;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class DynamoDbFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new DynamoDbFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return DynamoDbFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 4;
    }

    protected function getFixtureClasses(): array
    {
        return [
            DynamoDbFixture1::class => 'DynamoDbFixture1.php',
            DynamoDbFixture2::class => 'DynamoDbFixture2.php',
        ];
    }
}
