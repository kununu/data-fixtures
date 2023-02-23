<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Exception;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Executor\NonTransactionalConnectionExecutor;
use Kununu\DataFixtures\Tests\Utils\ConnectionUtilsTrait;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;

final class NonTransactionalConnectionExecutorTest extends AbstractExecutorTestCase
{
    use ConnectionUtilsTrait;

    private const SQL_1 = 'SET FOREIGN_KEY_CHECKS=0';
    private const SQL_2 = 'SET FOREIGN_KEY_CHECKS=1';

    private MockObject|Connection $connection;

    public function testThatExecutorIsNotTransactionalAndLoadsFixture(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method($this->getExecuteQueryMethodName($this->connection))
            ->willReturnCallback(
                fn (string $sql): int => match ($sql) {
                    self::SQL_1, self::SQL_2 => 1,
                    default => throw new LogicException(sprintf('Unknown SQL "%s"', $sql))
                }
            );

        $this->connection
            ->expects($this->never())
            ->method('beginTransaction');

        $this->connection
            ->expects($this->never())
            ->method('commit');

        $this->purger
            ->expects($this->never())
            ->method('purge');

        $fixture = $this->createMock(ConnectionFixtureInterface::class);
        $fixture
            ->expects($this->once())
            ->method('load')
            ->with($this->connection);

        $this->executor->execute([$fixture], true);
    }

    public function testThatExecutorIsNotTransactionalAndDoesNotRollbacks(): void
    {
        $this->expectException(Exception::class);

        $this->connection
            ->expects($this->exactly(2))
            ->method($this->getExecuteQueryMethodName($this->connection))
            ->willReturnCallback(
                fn (string $sql): int => match ($sql) {
                    self::SQL_1, self::SQL_2 => 1,
                    default => throw new LogicException(sprintf('Unknown SQL "%s"', $sql))
                }
            );

        $this->connection
            ->expects($this->never())
            ->method('beginTransaction');

        $this->connection
            ->expects($this->never())
            ->method('commit');

        $this->connection
            ->expects($this->never())
            ->method('rollBack');

        $this->purger
            ->expects($this->once())
            ->method('purge');

        $fixture = $this->createMock(ConnectionFixtureInterface::class);
        $fixture
            ->expects($this->once())
            ->method('load')
            ->with($this->connection)
            ->willThrowException(new Exception());

        $this->executor->execute([$fixture]);
    }

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->createMock(AbstractMySQLDriver::class));

        parent::setUp();
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new NonTransactionalConnectionExecutor($this->connection, $this->purger);
    }
}
