<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use Kununu\DataFixtures\Exception\InvalidConnectionPurgeModeException;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use PHPUnit\Framework\MockObject\MockObject;

final class ConnectionPurgerTest extends ConnectionPurgerTestCase
{
    public function testThatPurgerIsTransactionalAndCommits(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement())
            ->willReturn(1);

        $transactionStarted = false;
        $connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $connection
            ->expects($this->once())
            ->method('commit')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $this->assertTrue($transactionStarted);
            });

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatPurgerIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement())
            ->willReturn(1);

        $transactionStarted = false;
        $connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $connection
            ->expects($this->once())
            ->method('commit')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $this->assertTrue($transactionStarted);
                throw new Exception('Failed to commit!');
            });

        $connection
            ->expects($this->once())
            ->method('rollback');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock(true, []);

        $connection
            ->expects($this->never())
            ->method($this->getExecuteQueryMethodName($connection));

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(4))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement(1, self::TABLES, self::EXCLUDED_TABLES))
            ->willReturn(1);

        $purger = new ConnectionPurger(
            $connection,
            self::EXCLUDED_TABLES
        );

        $purger->purge();
    }

    public function testThatPurgesWithDeleteMode(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement())
            ->willReturn(1);

        $purger = new ConnectionPurger($connection);

        $purger->purge();
    }

    public function testThatPurgesWithTruncateMode(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock(false);

        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->exactly(3))
            ->method('getTruncateTableSQL')
            ->withConsecutive(
                ['table_1', true],
                ['table_2', true],
                ['table_3', true]
            )
            ->willReturnOnConsecutiveCalls(
                ...array_map(function($element) {
                    return $element[0];
                }, $this->getTruncateModeConnectionWithConsecutiveArguments())
            );

        $connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $connection
            ->expects($this->exactly(5))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement(2))
            ->willReturn(1);

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);
        $purger->purge();
    }

    public function testChangePurgeModeToDelete(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(1);

        $this->assertEquals(1, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToTruncate(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);

        $this->assertEquals(2, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToNotSupportedModeThrowsException(): void
    {
        $this->expectException(InvalidConnectionPurgeModeException::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);
        $purger->setPurgeMode(10);
    }
}
