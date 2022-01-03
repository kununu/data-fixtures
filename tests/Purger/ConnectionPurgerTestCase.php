<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kununu\DataFixtures\Tests\Utils\ConnectionUtilsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class ConnectionPurgerTestCase extends TestCase
{
    use ConnectionUtilsTrait;

    protected const TABLES = ['table_1', 'table_2', 'table_3'];
    protected const EXCLUDED_TABLES = ['table_4', 'table_2', 'table_5'];

    protected function getConnectionMock(bool $withPlatform = true, array $tables = self::TABLES): MockObject
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

    protected function getConsecutiveArgumentsForConnectionExecStatement(
        ?int $purgeMode = 1,
        ?array $tables = self::TABLES,
        ?array $excludedTables = []
    ): array {
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

    protected function getDeleteModeConnectionWithConsecutiveArguments(array $tables = self::TABLES, array $excludedTables = []): array
    {
        $return = [];

        foreach ($tables as $tableName) {
            if (!in_array($tableName, $excludedTables)) {
                $return[] = [sprintf('DELETE FROM %s', $tableName)];
            }
        }

        return $return;
    }

    protected function getTruncateModeConnectionWithConsecutiveArguments(array $tables = self::TABLES, array $excludedTables = []): array
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
