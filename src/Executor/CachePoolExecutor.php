<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolExecutor implements ExecutorInterface
{
    private $cache;

    private $purger;

    public function __construct(CacheItemPoolInterface $cache, PurgerInterface $purger)
    {
        $this->cache = $cache;
        $this->purger = $purger;
    }

    public function execute(array $fixtures, $append = false): void
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
