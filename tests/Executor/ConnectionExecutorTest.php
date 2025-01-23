<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Exception;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

final class ConnectionExecutorTest extends AbstractExecutorTestCase
{
    private const string SQL_1 = 'SET FOREIGN_KEY_CHECKS=0';
    private const string SQL_2 = 'SET FOREIGN_KEY_CHECKS=1';

    private MockObject&Connection $connection;

    public function testThatExecutorIsTransactionalAndCommits(): void
    {
        $this->connection
            ->expects(self::exactly(2))
            ->method('executeStatement')
            ->willReturnCallback(
                static fn(string $sql): int => match ($sql) {
                    self::SQL_1,
                    self::SQL_2 => 1,
                    default     => throw new LogicException(sprintf('Unknown SQL "%s"', $sql)),
                }
            );

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('commit');

        $this->purger
            ->expects(self::never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatExecutorIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);

        $this->connection
            ->expects(self::exactly(2))
            ->method('executeStatement')
            ->willReturnCallback(
                static fn(string $sql): int => match ($sql) {
                    self::SQL_1,
                    self::SQL_2 => 1,
                    default     => throw new LogicException(sprintf('Unknown SQL "%s"', $sql)),
                }
            );

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::once())
            ->method('commit')
            ->willThrowException(new Exception());

        $this->connection
            ->expects(self::once())
            ->method('rollBack');

        $this->purger
            ->expects(self::once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->connection
            ->expects(self::any())
            ->method('executeStatement')
            ->willReturn(1);

        $this->purger
            ->expects(self::never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->connection
            ->expects(self::any())
            ->method('executeStatement')
            ->willReturn(1);

        $this->purger
            ->expects(self::once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $this->connection
            ->expects(self::any())
            ->method('executeStatement')
            ->willReturn(1);

        $fixture1 = $this->createMock(ConnectionFixtureInterface::class);
        $fixture1
            ->expects(self::once())
            ->method('load')
            ->with($this->connection);

        $fixture2 = $this->createMock(ConnectionFixtureInterface::class);
        $fixture2
            ->expects(self::once())
            ->method('load')
            ->with($this->connection);

        $this->executor->execute([$fixture1, $fixture2]);
    }

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection
            ->expects(self::any())
            ->method('getDriver')
            ->willReturn($this->createMock(AbstractMySQLDriver::class));

        parent::setUp();
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new ConnectionExecutor($this->connection, $this->purger);
    }
}
