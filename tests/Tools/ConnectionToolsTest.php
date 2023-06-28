<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Logging\Middleware;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    /**
     * @dataProvider provideMySQLDrivers
     */
    public function testGetDisableForeignKeyChecksForMySQL(Driver $driver): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByDriver($driver),
        );
    }

    /**
     * @dataProvider provideMySQLDrivers
     */
    public function testGetEnableForeignKeyChecksForMySQL(Driver $driver): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByDriver($driver),
        );
    }

    /**
     * @dataProvider provideSQLiteDrivers
     */
    public function testGetDisableForeignKeyChecksForSqlite(Driver $driver): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByDriver($driver)
        );
    }

    /**
     * @dataProvider provideSQLiteDrivers
     */
    public function testGetEnableForeignKeyChecksForSqlite(Driver $driver): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = ON',
            $this->getEnableForeignKeysChecksStatementByDriver($driver)
        );
    }

    public function testGetEnableForeignKeyChecksForUnknownDriver(): void
    {
        $this->assertEquals(
            '',
            $this->getEnableForeignKeysChecksStatementByDriver($this->createMock(Driver::class))
        );
    }

    public function testGetDisableForeignKeyChecksForUnknownDriver(): void
    {
        $this->assertEquals(
            '',
            $this->getDisableForeignKeysChecksStatementByDriver($this->createMock(Driver::class))
        );
    }

    /**
     * @return array<string, array{Driver}>
     */
    public static function provideMySQLDrivers(): array
    {
        $abstractMySQLDriver = new class() extends AbstractMySQLDriver {
            public function connect(array $params)
            {
                return null;
            }
        };

        return [
            'abstract mysql driver'                               => [$abstractMySQLDriver],
            'abstract mysql driver wrapped in logging middleware' => [(new Middleware(new NullLogger()))->wrap($abstractMySQLDriver)],
        ];
    }

    /**
     * @return array<string, array{Driver}>
     */
    public static function provideSQLiteDrivers(): array
    {
        $abstractSQLiteDriver = new class() extends AbstractSQLiteDriver {
            public function connect(array $params)
            {
                return null;
            }
        };

        return [
            'abstract sqlite driver'                               => [$abstractSQLiteDriver],
            'abstract sqlite driver wrapped in logging middleware' => [(new Middleware(new NullLogger()))->wrap($abstractSQLiteDriver)],
        ];
    }
}
