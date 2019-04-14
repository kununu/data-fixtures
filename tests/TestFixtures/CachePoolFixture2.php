<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolFixture2 implements CachePoolFixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void
    {
    }
}
