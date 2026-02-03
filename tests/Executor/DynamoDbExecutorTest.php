<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Adapter\DynamoDbFixtureInterface;
use Kununu\DataFixtures\Executor\DynamoDbExecutor;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Tests\Utils\FakeDynamoDbClient;

final class DynamoDbExecutorTest extends AbstractExecutorTestCase
{
    private FakeDynamoDbClient $dynamoDbClient;

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
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $fixture1 = $this->createMock(DynamoDbFixtureInterface::class);
        $fixture1
            ->expects($this->once())
            ->method('load')
            ->with($this->dynamoDbClient);

        $fixture2 = $this->createMock(DynamoDbFixtureInterface::class);
        $fixture2
            ->expects($this->once())
            ->method('load')
            ->with($this->dynamoDbClient);

        $this->executor->execute([$fixture1, $fixture2]);
    }

    public function testThatFixturesAreLoadedWithAppend(): void
    {
        $fixture1 = $this->createMock(DynamoDbFixtureInterface::class);
        $fixture1
            ->expects($this->once())
            ->method('load')
            ->with($this->dynamoDbClient);

        $fixture2 = $this->createMock(DynamoDbFixtureInterface::class);
        $fixture2
            ->expects($this->once())
            ->method('load')
            ->with($this->dynamoDbClient);

        $this->purger
            ->expects($this->never())
            ->method('purge');

        $this->executor->execute([$fixture1, $fixture2], true);
    }

    public function testThatPurgeIsCalledBeforeLoadingFixtures(): void
    {
        $fixture = $this->createMock(DynamoDbFixtureInterface::class);

        $callOrder = [];

        $this->purger
            ->expects($this->once())
            ->method('purge')
            ->willReturnCallback(static function() use (&$callOrder): void {
                $callOrder[] = 'purge';
            });

        $fixture
            ->expects($this->once())
            ->method('load')
            ->willReturnCallback(static function() use (&$callOrder): void {
                $callOrder[] = 'load';
            });

        $this->executor->execute([$fixture]);

        self::assertSame(['purge', 'load'], $callOrder);
    }

    public function testExecuteWithEmptyFixtures(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testExecuteWithEmptyFixturesAndAppend(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    protected function setUp(): void
    {
        $this->dynamoDbClient = new FakeDynamoDbClient();

        parent::setUp();
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new DynamoDbExecutor($this->dynamoDbClient, $this->purger);
    }
}
