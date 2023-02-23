<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Executor\CachePoolExecutor;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemPoolInterface;

final class CachePoolExecutorTest extends AbstractExecutorTestCase
{
    private MockObject|CacheItemPoolInterface $cache;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(CachePoolFixtureInterface::class);
        $fixture1->expects($this->once())->method('load')->with($this->cache);

        $fixture2 = $this->createMock(CachePoolFixtureInterface::class);
        $fixture2->expects($this->once())->method('load')->with($this->cache);

        $this->executor->execute([$fixture1, $fixture2]);
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new CachePoolExecutor($this->cache, $this->purger);
    }

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        parent::setUp();
    }
}
