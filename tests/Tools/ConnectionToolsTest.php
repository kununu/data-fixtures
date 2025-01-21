<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Driver\Connection;
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
    public function testGetDisableForeignKeyChecksForMySQL(callable|string $driver): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    #[DataProvider('mysqlDataProvider')]
    public function testGetEnableForeignKeyChecksForMySQL(callable|string $driver): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetDisableForeignKeyChecksForSqlite(callable|string $driver): void
    {
        self::assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver))
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetEnableForeignKeyChecksForSqlite(callable|string $driver): void
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

    /** @return array<string, array{callable|string}> */
    public static function mysqlDataProvider(): array
    {
        $result = [];

        if (class_exists(AbstractMySQLPlatform::class)) {
            $driverBuilder = self::getMySqlDriverBuilder();
            $wrappedDriverBuilder = self::getWrappedDriverBuilder($driverBuilder);

            $result = [
                'abstract_mysql_driver_instance'                      => [$driverBuilder],
                'abstract_mysql_driver_wrapped_in_logging_middleware' => [$wrappedDriverBuilder],
            ];
        }

        return array_merge(
            [
                'abstract_mysql_driver_class_name' => [AbstractMySQLDriver::class],
            ],
            $result
        );
    }

    /** @return array<string, array{callable|string}> */
    public static function sqliteDataProvider(): array
    {
        $result = [];

        if (class_exists(SqlitePlatform::class)) {
            $driverBuilder = self::getSQLiteDriverBuilder();
            $wrappedDriverBuilder = self::getWrappedDriverBuilder($driverBuilder);

            $result = [
                'abstract_sqlite_driver_instance'                      => [$driverBuilder],
                'abstract_sqlite_driver_wrapped_in_logging_middleware' => [$wrappedDriverBuilder],
            ];
        }

        return array_merge(
            [
                'abstract_sqlite_driver_class_name' => [AbstractSQLiteDriver::class],
            ],
            $result
        );
    }

    public function getDriverConnectionMock(): MockObject&Connection
    {
        return $this->createMock(Connection::class);
    }

    private static function getMySqlDriverBuilder(): callable
    {
        return fn(ConnectionToolsTest $testCase): Driver => new class($testCase) extends AbstractMySQLDriver {
            public function __construct(private readonly ConnectionToolsTest $testCase)
            {
            }

            public function connect(array $params): Connection
            {
                return $this->testCase->getDriverConnectionMock();
            }
        };
    }

    private static function getSQLiteDriverBuilder(): callable
    {
        return fn(ConnectionToolsTest $testCase): Driver => new class($testCase) extends AbstractSQLiteDriver {
            public function __construct(private readonly ConnectionToolsTest $testCase)
            {
            }

            public function connect(array $params): Connection
            {
                return $this->testCase->getDriverConnectionMock();
            }
        };
    }

    private static function getWrappedDriverBuilder(callable $driverBuilder): callable
    {
        return function(ConnectionToolsTest $testCase) use ($driverBuilder): Driver {
            $driver = $driverBuilder($testCase);

            return (new Middleware(new NullLogger()))->wrap($driver);
        };
    }

    private function getDriver(callable|string $driver): MockObject|Driver
    {
        return match (true) {
            is_callable($driver) => $driver($this),
            default              => $this->createMock($driver),
        };
    }
}
