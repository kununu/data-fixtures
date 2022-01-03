<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Exception;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Executor\NonTransactionalConnectionExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use Kununu\DataFixtures\Tests\Utils\ConnectionUtilsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NonTransactionalConnectionExecutorTest extends TestCase
{
    use ConnectionUtilsTrait;

    /** @var Connection|MockObject */
    private $connection;

    /** @var PurgerInterface|MockObject */
    private $purger;

    public function testThatExecutorIsNotTransactionalAndLoadsFixture(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method($this->getExecuteQueryMethodName($this->connection))
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1'])
            ->willReturn(1);

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

        $executor = new NonTransactionalConnectionExecutor($this->connection, $this->purger);

        $executor->execute([$fixture], true);
    }

    public function testThatExecutorIsNotTransactionalAndDoesNotRollbacks(): void
    {
        $this->expectException(Exception::class);

        $this->connection
            ->expects($this->exactly(2))
            ->method($this->getExecuteQueryMethodName($this->connection))
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1'])
            ->willReturn(1);

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

        $executor = new NonTransactionalConnectionExecutor($this->connection, $this->purger);

        $executor->execute([$fixture]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createMock(Connection::class);
        $this->connection
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->createMock(AbstractMySQLDriver::class));
        $this->purger = $this->createMock(PurgerInterface::class);
    }
}
