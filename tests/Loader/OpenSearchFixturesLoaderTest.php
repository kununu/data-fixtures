<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Loader\OpenSearchFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\OpenSearchFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\OpenSearchFixture2;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class OpenSearchFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new OpenSearchFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return OpenSearchFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 8;
    }

    protected function getFixtureClasses(): array
    {
        return [
            OpenSearchFixture1::class => 'OpenSearchFixture1.php',
            OpenSearchFixture2::class => 'OpenSearchFixture2.php',
        ];
    }
}
