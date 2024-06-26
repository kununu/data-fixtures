<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolExecutor implements ExecutorInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly PurgerInterface $purger
    ) {
    }

    public function execute(array $fixtures, bool $append = false): void
    {
        if ($append === false) {
            $this->purger->purge();
        }

        foreach ($fixtures as $fixture) {
            $this->load($fixture);
        }
    }

    private function load(CachePoolFixtureInterface $fixture): void
    {
        $fixture->load($this->cache);
    }
}
