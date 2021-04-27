<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Psr\Cache\CacheItemPoolInterface;

final class CachePoolPurger implements PurgerInterface
{
    private $cachePool;

    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    public function purge(): void
    {
        if (!$this->cachePool->clear()) {
            throw new \Exception(sprintf('Failed to purge cache pool "%s"', get_class($this->cachePool)));
        }
    }
}
