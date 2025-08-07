<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Exception\PurgeFailedException;
use Kununu\DataFixtures\Purger\CachePoolPurger;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolPurgerTest extends AbstractPurgerTestCase
{
    private MockObject&CacheItemPoolInterface $cache;

    public function testThatCacheItemPoolIsPurged(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $this->purger->purge();
    }

    public function testThatWhenCacheItemPoolFailsToPurgeThenAnExceptionIsThrown(): void
    {
        $this->expectException(PurgeFailedException::class);

        $this->cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(false);

        $this->purger->purge();
    }

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        parent::setUp();
    }

    protected function getPurger(): PurgerInterface
    {
        return new CachePoolPurger($this->cache);
    }
}
