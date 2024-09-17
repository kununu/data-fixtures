<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CachePoolExecutor implements ExecutorInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private PurgerInterface $purger,
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
