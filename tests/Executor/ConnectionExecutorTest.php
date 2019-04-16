<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Executor\ConnectionExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionExecutorTest extends TestCase
{
    /** @var Connection|MockObject */
    private $connection;

    /** @var PurgerInterface|MockObject */
    private $purger;

    public function testThatExecutorIsTransactionalAndCommits() : void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method('exec')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1']);

        $this->connection
            ->expects($this->once())
            ->method('beginTransaction');

        $this->connection
            ->expects($this->once())
            ->method('commit');

        $this->purger
            ->expects($this->never())
            ->method('purge');

        $executor = new ConnectionExecutor($this->connection, $this->purger);

        $executor->execute([], true);
    }

    public function testThatExecutorIsTransactionalAndRollbacks() : void
    {
        $this->expectException(\Exception::class);

        $this->connection
            ->expects($this->exactly(2))
            ->method('exec')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1']);

        $this->connection
            ->expects($this->once())
            ->method('beginTransaction');

        $this->connection
            ->expects($this->once())
            ->method('commit')
            ->willThrowException(new \Exception());

        $this->connection
            ->expects($this->once())
            ->method('rollBack');

        $this->purger
            ->expects($this->once())
            ->method('purge');

        $executor = new ConnectionExecutor($this->connection, $this->purger);

        $executor->execute([]);
    }

    public function testThatDoesNotPurgesWhenAppendIsEnabled() : void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $executor = new ConnectionExecutor($this->connection, $this->purger);

        $executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled() : void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $executor = new ConnectionExecutor($this->connection, $this->purger);

        $executor->execute([]);
    }

    public function testThatFixturesAreLoaded() : void
    {
        $fixture1 = $this->createMock(ConnectionFixtureInterface::class);
        $fixture1->expects($this->once())->method('load')->with($this->connection);

        $fixture2 = $this->createMock(ConnectionFixtureInterface::class);
        $fixture2->expects($this->once())->method('load')->with($this->connection);

        $executor = new ConnectionExecutor($this->connection, $this->purger);

        $executor->execute([$fixture1, $fixture2]);
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
