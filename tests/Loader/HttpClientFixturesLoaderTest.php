<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture2;

final class HttpClientFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new HttpClientFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return HttpClientFixtureInterface::class;
    }

    protected function getFixtureClasses(): array
    {
        return [
            HttpClientFixture1::class => 'HttpClientFixture1.php',
            HttpClientFixture2::class => 'HttpClientFixture2.php',
        ];
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 6;
    }
}
