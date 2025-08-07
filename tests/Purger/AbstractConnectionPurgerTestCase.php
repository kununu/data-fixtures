<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kununu\DataFixtures\Purger\PurgeMode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractConnectionPurgerTestCase extends TestCase
{
    protected const string TABLE_1 = 'table_1';
    protected const string TABLE_2 = 'table_2';
    protected const string TABLE_3 = 'table_3';
    protected const string TABLE_4 = 'table_4';
    protected const string TABLE_5 = 'table_5';
    protected const array TABLES = [self::TABLE_1, self::TABLE_2, self::TABLE_3];
    protected const array EXCLUDED_TABLES = [self::TABLE_4, self::TABLE_2, self::TABLE_5];

    protected function getConnectionMock(bool $withPlatform = true, array $tables = self::TABLES): MockObject&Connection
    {
        $connection = $this->createMock(Connection::class);

        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager
            ->expects(self::any())
            ->method('listTableNames')
            ->willReturn($tables);

        $connection
            ->expects(self::any())
            ->method('createSchemaManager')
            ->willReturn($schemaManager);

        $connection
            ->expects(self::any())
            ->method('quoteIdentifier')
            ->willReturnCallback(fn(string $str): string => sprintf('`%s`', $str));

        if ($withPlatform) {
            $connection
                ->expects(self::any())
                ->method('getDatabasePlatform')
                ->willReturn($this->createMock(AbstractMySQLPlatform::class));
        }

        return $connection;
    }

    protected function getConsecutiveArgumentsForConnectionExecStatement(
        PurgeMode $purgeMode = PurgeMode::Delete,
        ?array $tables = self::TABLES,
        ?array $excludedTables = [],
        bool $itemsAsArray = true,
    ): array {
        $purgeStatements = match ($purgeMode) {
            PurgeMode::Delete   => $this->getDeleteModeConsecutiveArguments($tables, $excludedTables, $itemsAsArray),
            PurgeMode::Truncate => $this->getTruncateModeConsecutiveArguments($tables, $excludedTables, $itemsAsArray),
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
        bool $itemsAsArray = true,
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
        bool $itemsAsArray = true,
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
