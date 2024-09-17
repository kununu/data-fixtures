<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Executor\HttpClientExecutor;
use Kununu\DataFixtures\Tools\FixturesHttpClientInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class HttpClientExecutorTest extends AbstractExecutorTestCase
{
    private MockObject&FixturesHttpClientInterface $httpClient;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects(self::never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects(self::once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(HttpClientFixtureInterface::class);
        $fixture1
            ->expects(self::once())
            ->method('load')
            ->with($this->httpClient);

        $fixture2 = $this->createMock(HttpClientFixtureInterface::class);
        $fixture2
            ->expects(self::once())
            ->method('load')
            ->with($this->httpClient);

        $this->executor->execute([$fixture1, $fixture2], true);
    }

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(FixturesHttpClientInterface::class);

        parent::setUp();
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new HttpClientExecutor($this->httpClient, $this->purger);
    }
}
