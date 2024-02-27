<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use Kununu\DataFixtures\Exception\InvalidConnectionPurgeModeException;
use Kununu\DataFixtures\Purger\ConnectionPurger;

final class ConnectionPurgerTest extends AbstractConnectionPurgerTestCase
{
    public function testThatPurgerIsTransactionalAndCommits(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(itemsAsArray: false)
                    )
                )
            )
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

        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(itemsAsArray: false)
                    )
                )
            )
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
        $connection = $this->getConnectionMock(true, []);

        $connection
            ->expects($this->never())
            ->method('executeStatement');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(4))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(
                            excludedTables: self::EXCLUDED_TABLES,
                            itemsAsArray: false
                        )
                    )
                )
            )
            ->willReturn(1);

        $purger = new ConnectionPurger($connection, self::EXCLUDED_TABLES);

        $purger->purge();
    }

    public function testThatPurgesWithDeleteMode(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(itemsAsArray: false)
                    )
                )
            )
            ->willReturn(1);

        $purger = new ConnectionPurger($connection);

        $purger->purge();
    }

    public function testThatPurgesWithTruncateMode(): void
    {
        $connection = $this->getConnectionMock(false);

        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->exactly(3))
            ->method('getTruncateTableSQL')
            ->with(
                $this->callback(
                    fn(string $table): bool => in_array($table, [self::TABLE_1, self::TABLE_2, self::TABLE_3])
                )
            )
            ->willReturnOnConsecutiveCalls(
                ...array_map(fn($element) => $element[0], $this->getTruncateModeConsecutiveArguments())
            );

        $connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $connection
            ->expects($this->exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(2, itemsAsArray: false)
                    )
                )
            )
            ->willReturn(1);

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);
        $purger->purge();
    }

    public function testChangePurgeModeToDelete(): void
    {
        $purger = new ConnectionPurger($this->getConnectionMock());

        $purger->setPurgeMode(1);

        $this->assertEquals(1, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToTruncate(): void
    {
        $purger = new ConnectionPurger($this->getConnectionMock());

        $purger->setPurgeMode(2);

        $this->assertEquals(2, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToNotSupportedModeThrowsException(): void
    {
        $this->expectException(InvalidConnectionPurgeModeException::class);

        $purger = new ConnectionPurger($this->getConnectionMock());
        $purger->setPurgeMode(10);
    }
}
