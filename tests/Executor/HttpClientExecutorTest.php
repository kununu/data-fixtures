<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\TestCase;

final class HttpClientExecutorTest extends TestCase
{
    private $httpClient;
    private $purger;
    private $executor;

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
        $fixture1 = $this->createMock(HttpClientFixtureInterface::class);
        $fixture1
            ->expects($this->once())
            ->method('load')
            ->with($this->httpClient);

        $fixture2 = $this->createMock(HttpClientFixtureInterface::class);
        $fixture2
            ->expects($this->once())
            ->method('load')
            ->with($this->httpClient);

        $this->executor->execute([$fixture1, $fixture2], true);
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(FixturesHttpClientInterface::class);
        $this->purger = $this->createMock(PurgerInterface::class);
        $this->executor = new HttpClientExecutor($this->httpClient, $this->purger);
    }
}
