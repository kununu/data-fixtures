<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    #[DataProvider('mysqlDataProvider')]
    public function testGetDisableForeignKeyChecksForMySQL(Driver|string $driver): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    #[DataProvider('mysqlDataProvider')]
    public function testGetEnableForeignKeyChecksForMySQL(Driver|string $driver): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetDisableForeignKeyChecksForSqlite(Driver|string $driver): void
    {
        self::assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver))
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetEnableForeignKeyChecksForSqlite(Driver|string $driver): void
    {
        self::assertEquals(
            'PRAGMA foreign_keys = ON',
            $this->getEnableForeignKeysChecksStatementByDriver($this->getDriver($driver))
        );
    }

    public function testGetEnableForeignKeyChecksForUnknownDriver(): void
    {
        self::assertEquals(
            '',
            $this->getEnableForeignKeysChecksStatementByDriver($this->createMock(Driver::class))
        );
    }

    public function testGetDisableForeignKeyChecksForUnknownDriver(): void
    {
        self::assertEquals(
            '',
            $this->getDisableForeignKeysChecksStatementByDriver($this->createMock(Driver::class))
        );
    }

    /** @return array<string, array{Driver|string}> */
    public static function mysqlDataProvider(): array
    {
        if (class_exists(AbstractMySQLPlatform::class)) {
            $abstractMySQLDriver = new class extends AbstractMySQLDriver {
                public function connect(array $params): null
                {
                    return null;
                }
            };

            return [
                'abstract_mysql_driver'                               => [$abstractMySQLDriver],
                'abstract_mysql_driver_wrapped_in_logging_middleware' => [
                    (new Middleware(new NullLogger()))->wrap($abstractMySQLDriver),
                ],
            ];
        }

        return [
            'abstract_mysql_driver' => [AbstractMySQLDriver::class],
        ];
    }

    /** @return array<string, array{Driver|string}> */
    public static function sqliteDataProvider(): array
    {
        if (class_exists(SqlitePlatform::class)) {
            $abstractSQLiteDriver = new class extends AbstractSQLiteDriver {
                public function connect(array $params)
                {
                    return null;
                }
            };

            return [
                'abstract_sqlite_driver'                               => [$abstractSQLiteDriver],
                'abstract_sqlite_driver wrapped_in_logging_middleware' => [
                    (new Middleware(new NullLogger()))->wrap($abstractSQLiteDriver),
                ],
            ];
        }

        return [
            'abstract sqlite driver' => [AbstractSQLiteDriver::class],
        ];
    }

    private function getDriver(Driver|string $driver): MockObject|Driver
    {
        return is_string($driver) ? $this->createMock($driver) : $driver;
    }
}
