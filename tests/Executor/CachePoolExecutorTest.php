<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolExecutorTest extends TestCase
{
    /** @var CacheItemPoolInterface|MockObject */
    private $cache;

    /** @var PurgerInterface|MockObject */
    private $purger;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $executor = new CachePoolExecutor($this->cache, $this->purger);

        $executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $executor = new CachePoolExecutor($this->cache, $this->purger);

        $executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(CachePoolFixtureInterface::class);
        $fixture1->expects($this->once())->method('load')->with($this->cache);

        $fixture2 = $this->createMock(CachePoolFixtureInterface::class);
        $fixture2->expects($this->once())->method('load')->with($this->cache);

        $executor = new CachePoolExecutor($this->cache, $this->purger);

        $executor->execute([$fixture1, $fixture2]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->purger = $this->createMock(PurgerInterface::class);
    }
}
