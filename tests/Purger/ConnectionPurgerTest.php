<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Exception;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\DataFixtures\Purger\PurgeMode;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class ConnectionPurgerTest extends AbstractConnectionPurgerTestCase
{
    public function testPurgerIsTransactionalAndCommits(): void
    {
        $this->configureTables();

        $this->connection
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

        $this->connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturnCallback(static function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $this->connection
            ->expects($this->once())
            ->method('commit')
            ->willReturnCallback(static function() use (&$transactionStarted): void {
                self::assertTrue($transactionStarted);
            });

        $this->purge();
    }

    public function testPurgerIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);

        $this->configureTables();

        $this->connection
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
        $this->connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturnCallback(static function() use (&$transactionStarted): void {
                $transactionStarted = true;
            });

        $this->connection
            ->expects($this->once())
            ->method('commit')
            ->willReturnCallback(static function() use (&$transactionStarted): void {
                self::assertTrue($transactionStarted);

                throw new Exception('Failed to commit!');
            });

        $this->connection
            ->expects($this->once())
            ->method('rollback');

        $this->purge();
    }

    public function testWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        $this->configureTables([]);

        $this->connection
            ->expects($this->never())
            ->method('executeStatement');

        $this->purge();
    }

    public function testExcludedTablesAreNotPurged(): void
    {
        $this->configureTables();

        $this->connection
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

        $this->purge(self::EXCLUDED_TABLES);
    }

    public function testPurgesWithDeleteMode(): void
    {
        $this->configureTables();

        $this->connection
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

        $this->purge();
    }

    public function testPurgesWithTruncateMode(): void
    {
        $this->configureTables();

        $this->platform
            ->expects($this->exactly(3))
            ->method('getTruncateTableSQL')
            ->with(
                $this->callback(
                    static fn(string $table): bool => in_array($table, [self::TABLE_1, self::TABLE_2, self::TABLE_3])
                )
            )
            ->willReturnOnConsecutiveCalls(
                // @phpstan-ignore argument.named
                ...array_map(static fn($element) => $element[0], $this->getTruncateModeConsecutiveArguments())
            );

        $this->connection
            ->expects($this->exactly(5))
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

        $this->purge(purgeMode: PurgeMode::Truncate);
    }

    private function purge(array $excludedTables = [], PurgeMode $purgeMode = PurgeMode::Delete): void
    {
        (new ConnectionPurger($this->connection, $excludedTables, purgeMode: $purgeMode))->purge();
    }
}
