<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Kununu\DataFixtures\Exception\PurgeFailedException;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolPurger implements PurgerInterface
{
    public function __construct(private readonly CacheItemPoolInterface $cachePool)
    {
    }

    public function purge(): void
    {
        if (!$this->cachePool->clear()) {
            throw new PurgeFailedException(sprintf('Failed to purge cache pool "%s"', $this->cachePool::class));
        }
    }
}
