<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger\Cache;

use Kununu\DataFixtures\Purger\CachePoolPurger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolPurgerTest extends TestCase
{
    /** @var MockObject|CacheItemPoolInterface */
    private $cache;

    public function testThatCacheItemPoolIsPurged(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $purger = new CachePoolPurger($this->cache);
        $purger->purge();
    }

    public function testThatWhenCacheItemPoolFailsToPurgeThenAnExceptionIsThrown(): void
    {
        $this->expectException(\Exception::class);

        $this->cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(false);

        $purger = new CachePoolPurger($this->cache);
        $purger->purge();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheItemPoolInterface::class);
    }
}
