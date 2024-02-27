<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractConnectionPurgerTestCase extends TestCase
{
    protected const TABLE_1 = 'table_1';
    protected const TABLE_2 = 'table_2';
    protected const TABLE_3 = 'table_3';
    protected const TABLE_4 = 'table_4';
    protected const TABLE_5 = 'table_5';
    protected const TABLES = [self::TABLE_1, self::TABLE_2, self::TABLE_3];
    protected const EXCLUDED_TABLES = [self::TABLE_4, self::TABLE_2, self::TABLE_5];

    protected function getConnectionMock(bool $withPlatform = true, array $tables = self::TABLES): MockObject|Connection
    {
        $connection = $this->createMock(Connection::class);

        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager
            ->expects($this->any())
            ->method('listTableNames')
            ->willReturn($tables);

        $connection
            ->expects($this->any())
            ->method('createSchemaManager')
            ->willReturn($schemaManager);

        $connection
            ->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->createMock(AbstractMySQLDriver::class));

        $connection
            ->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback(fn(string $str): string => sprintf('`%s`', $str));

        if ($withPlatform) {
            $connection
                ->expects($this->any())
                ->method('getDatabasePlatform')
                ->willReturn($this->createMock(AbstractPlatform::class));
        }

        return $connection;
    }

    protected function getConsecutiveArgumentsForConnectionExecStatement(
        ?int $purgeMode = 1,
        ?array $tables = self::TABLES,
        ?array $excludedTables = [],
        bool $itemsAsArray = true
    ): array {
        $purgeStatements = match ($purgeMode) {
            // PURGE_MODE_DELETE
            1       => $this->getDeleteModeConsecutiveArguments($tables, $excludedTables, $itemsAsArray),
            // PURGE_MODE_TRUNCATE
            2       => $this->getTruncateModeConsecutiveArguments($tables, $excludedTables, $itemsAsArray),
            default => []
        };

        $disableForeignKeyChecks = 'SET FOREIGN_KEY_CHECKS=0';
        $enableForeignKeyChecks = 'SET FOREIGN_KEY_CHECKS=1';

        return array_merge(
            [$itemsAsArray ? [$disableForeignKeyChecks] : $disableForeignKeyChecks],
            $purgeStatements,
            [$itemsAsArray ? [$enableForeignKeyChecks] : $enableForeignKeyChecks]
        );
    }

    protected function getDeleteModeConsecutiveArguments(
        array $tables = self::TABLES,
        array $excludedTables = [],
        bool $itemsAsArray = true
    ): array {
        $return = [];

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                $statement = sprintf('DELETE FROM `%s`', $tableName);
                $return[] = $itemsAsArray ? [$statement] : $statement;
            }
        }

        return $return;
    }

    protected function getTruncateModeConsecutiveArguments(
        array $tables = self::TABLES,
        array $excludedTables = [],
        bool $itemsAsArray = true
    ): array {
        $return = [];

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                $statement = sprintf('TRUNCATE `%s`', $tableName);
                $return[] = $itemsAsArray ? [$statement] : $statement;
            }
        }

        return $return;
    }
}
