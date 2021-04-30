<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Exception;
use Kununu\DataFixtures\Exception\InvalidConnectionPurgeModeException;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use Kununu\DataFixtures\Tests\Utils\ConnectionUtilsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionPurgerTest extends TestCase
{
    use ConnectionUtilsTrait;

    private const TABLES = ['table_1', 'table_2', 'table_3'];
    private const EXCLUDED_TABLES = ['table_4', 'table_2', 'table_5'];

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

    private function getConnectionMock(bool $withPlatform = true, array $tables = self::TABLES): MockObject
    {
        $connection = $this->createMock(Connection::class);

        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn($tables);

        // To support doctrine/dbal ^2.9 and ^3.1
        if (method_exists($connection, 'createSchemaManager')) {
            $connection->expects($this->any())->method('createSchemaManager')->willReturn($schemaManager);
        } else {
            $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);
        }

        $connection->expects($this->any())->method('getDriver')->willReturn($this->createMock(AbstractMySQLDriver::class));
        $connection->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);

        if ($withPlatform) {
            $connection->expects($this->any())->method('getDatabasePlatform')->willReturn($this->createMock(AbstractPlatform::class));
        }

        return $connection;
    }

    private function getConsecutiveArgumentsForConnectionExecStatement(?int $purgeMode = 1, ?array $tables = self::TABLES, ?array $excludedTables = []): array
    {
        $purgeStatements = [];

        switch ($purgeMode) {
            case 1: // PURGE_MODE_DELETE
                $purgeStatements = $this->getDeleteModeConnectionWithConsecutiveArguments($tables, $excludedTables);
                break;
            case 2: // PURGE_MODE_TRUNCATE
                $purgeStatements = $this->getTruncateModeConnectionWithConsecutiveArguments($tables, $excludedTables);
                break;
            default:
                break;
        }

        return array_merge(
            [['SET FOREIGN_KEY_CHECKS=0']],
            $purgeStatements,
            [['SET FOREIGN_KEY_CHECKS=1']]
        );
    }

    private function getDeleteModeConnectionWithConsecutiveArguments(array $tables = self::TABLES, array $excludedTables = []): array
    {
        $return = [];

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                $return[] = [sprintf('DELETE FROM %s', $tableName)];
            }
        }

        return $return;
    }

    private function getTruncateModeConnectionWithConsecutiveArguments(array $tables = self::TABLES, array $excludedTables = []): array
    {
        $return = [];

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                $return[] = [sprintf('TRUNCATE %s', $tableName)];
            }
        }

        return $return;
    }
}
