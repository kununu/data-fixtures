<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolFixture1 implements CachePoolFixtureInterface, InitializableFixtureInterface
{
    private ?int $arg1 = null;
    private ?array $arg2 = null;

    public function load(CacheItemPoolInterface $cachePool): void
    {
    }

    public function initializeFixture(mixed ...$args): void
    {
        foreach ($args as $index => $arg) {
            if (0 === $index && is_int($arg)) {
                $this->arg1 = $arg;
            }

            if (1 === $index && is_array($arg)) {
                $this->arg2 = $arg;
            }
        }
    }

    public function arg1(): ?int
    {
        return $this->arg1;
    }

    public function arg2(): ?array
    {
        return $this->arg2;
    }
}
