<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Exception;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Executor\NonTransactionalConnectionExecutor;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

final class NonTransactionalConnectionExecutorTest extends AbstractExecutorTestCase
{
    private const string SQL_1 = 'SET FOREIGN_KEY_CHECKS=0';
    private const string SQL_2 = 'SET FOREIGN_KEY_CHECKS=1';

    private MockObject&Connection $connection;

    public function testThatExecutorIsNotTransactionalAndLoadsFixture(): void
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
            ->expects(self::never())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::never())
            ->method('commit');

        $this->purger
            ->expects(self::never())
            ->method('purge');

        $fixture = $this->createMock(ConnectionFixtureInterface::class);
        $fixture
            ->expects(self::once())
            ->method('load')
            ->with($this->connection);

        $this->executor->execute([$fixture], true);
    }

    public function testThatExecutorIsNotTransactionalAndDoesNotRollbacks(): void
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
            ->expects(self::never())
            ->method('beginTransaction');

        $this->connection
            ->expects(self::never())
            ->method('commit');

        $this->connection
            ->expects(self::never())
            ->method('rollBack');

        $this->purger
            ->expects(self::once())
            ->method('purge');

        $fixture = $this->createMock(ConnectionFixtureInterface::class);
        $fixture
            ->expects(self::once())
            ->method('load')
            ->with($this->connection)
            ->willThrowException(new Exception());

        $this->executor->execute([$fixture]);
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
        return new NonTransactionalConnectionExecutor($this->connection, $this->purger);
    }
}
