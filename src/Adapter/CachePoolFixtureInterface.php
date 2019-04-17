<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter;

use Kununu\DataFixtures\FixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

interface CachePoolFixtureInterface extends FixtureInterface
{
    public function load(CacheItemPoolInterface $cachePool): void;
}
