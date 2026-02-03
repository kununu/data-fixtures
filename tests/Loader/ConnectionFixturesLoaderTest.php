<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture3;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class ConnectionFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new ConnectionFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return ConnectionFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 8;
    }

    protected function getFixtureClasses(): array
    {
        return [
            ConnectionFixture1::class => 'ConnectionFixture1.php',
            ConnectionFixture2::class => 'ConnectionFixture2.php',
            ConnectionFixture3::class => 'ConnectionFixture3.php',
        ];
    }
}
