<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\DataFixtures\Purger\PurgeMode;

final class ConnectionPurgerTest extends AbstractConnectionPurgerTestCase
{
    public function testThatPurgerIsTransactionalAndCommits(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects(self::exactly(5))
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
            ->expects(self::once())
            ->method('beginTransaction')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $connection
            ->expects(self::once())
            ->method('commit')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                self::assertTrue($transactionStarted);
            });

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatPurgerIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);

        $connection = $this->getConnectionMock();

        $connection
            ->expects(self::exactly(5))
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
            ->expects(self::once())
            ->method('beginTransaction')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $connection
            ->expects(self::once())
            ->method('commit')
            ->willReturnCallback(function() use (&$transactionStarted): void {
                self::assertTrue($transactionStarted);

                throw new Exception('Failed to commit!');
            });

        $connection
            ->expects(self::once())
            ->method('rollback');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        $connection = $this->getConnectionMock(true, []);

        $connection
            ->expects(self::never())
            ->method('executeStatement');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects(self::exactly(4))
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
            ->expects(self::exactly(5))
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
            ->expects(self::exactly(3))
            ->method('getTruncateTableSQL')
            ->with(
                $this->callback(
                    static fn(string $table): bool => in_array($table, [self::TABLE_1, self::TABLE_2, self::TABLE_3])
                )
            )
            ->willReturnOnConsecutiveCalls(
                ...array_map(fn($element) => $element[0], $this->getTruncateModeConsecutiveArguments())
            );

        $connection
            ->expects(self::any())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $connection
            ->expects(self::exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(
                            purgeMode: PurgeMode::Truncate,
                            itemsAsArray: false
                        )
                    )
                )
            )
            ->willReturn(1);

        $purger = new ConnectionPurger($connection, purgeMode: PurgeMode::Truncate);
        $purger->purge();
    }
}
