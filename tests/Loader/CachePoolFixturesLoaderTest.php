<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\CachePoolFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\CachePoolFixture2;

final class CachePoolFixturesLoaderTest extends AbstractLoaderTestCase
{
    protected function getLoader(): LoaderInterface
    {
        return new CachePoolFixturesLoader();
    }

    protected function getFixtureInterfaceName(): string
    {
        return CachePoolFixtureInterface::class;
    }

    protected function expectedNumberOfFixturesFromDirectory(): int
    {
        return 4;
    }

    protected function getFixtureClasses(): array
    {
        return [
            CachePoolFixture1::class => 'CachePoolFixture1.php',
            CachePoolFixture2::class => 'CachePoolFixture2.php',
        ];
    }

    protected function initializeLoadFromFileFixtures(): void
    {
        $this->loader->registerInitializableFixture(
            CachePoolFixture1::class,
            2020,
            [
                'id'    => 1,
                'name'  => 'Test Subject',
                'value' => 1.35,
            ]
        );
    }

    protected function performExtraLoadFromFileFixturesAssertions(): void
    {
        $loadedFixtures = $this->loader->getFixtures();

        self::assertArrayHasKey(CachePoolFixture1::class, $loadedFixtures);

        $cachePoolFixture1 = $loadedFixtures[CachePoolFixture1::class];

        self::assertInstanceOf(CachePoolFixture1::class, $cachePoolFixture1);
        self::assertEquals(2020, $cachePoolFixture1->arg1());
        self::assertEquals(
            [
                'id'    => 1,
                'name'  => 'Test Subject',
                'value' => 1.35,
            ],
            $cachePoolFixture1->arg2()
        );
    }
}
