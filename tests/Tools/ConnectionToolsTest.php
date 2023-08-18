<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Logging\Middleware;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    /** @dataProvider providerMySQLDrivers */
    public function testGetDisableForeignKeyChecksForMySQL(Driver|string $driver): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    /** @dataProvider providerMySQLDrivers */
    public function testGetEnableForeignKeyChecksForMySQL(Driver|string $driver): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByDriver($this->getDriver($driver)),
        );
    }

    /** @dataProvider providerSQLiteDrivers */
    public function testGetDisableForeignKeyChecksForSqlite(Driver|string $driver): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByDriver($this->getDriver($driver))
        );
    }

    /** @dataProvider providerSQLiteDrivers */
    public function testGetEnableForeignKeyChecksForSqlite(Driver|string $driver): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = ON',
            $this->getEnableForeignKeysChecksStatementByDriver($this->getDriver($driver))
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

    /** @return array<string, array{Driver|string}> */
    public static function providerMySQLDrivers(): array
    {
        if (self::dbalSupportsAbstractMySQLPlatform()) {
            $abstractMySQLDriver = new class() extends AbstractMySQLDriver {
                public function connect(array $params)
                {
                    return null;
                }
            };

            return [
                'abstract mysql driver'                               => [$abstractMySQLDriver],
                'abstract mysql driver wrapped in logging middleware' => [
                    (new Middleware(new NullLogger()))->wrap($abstractMySQLDriver),
                ],
            ];
        }

        return [
            'abstract mysql driver' => [AbstractMySQLDriver::class],
        ];
    }

    /**
     * @return array<string, array{Driver}>
     */
    public static function providerSQLiteDrivers(): array
    {
        if (self::dbalSupportsAbstractMySQLPlatform()) {
            $abstractSQLiteDriver = new class() extends AbstractSQLiteDriver {
                public function connect(array $params)
                {
                    return null;
                }
            };

            return [
                'abstract sqlite driver'                               => [$abstractSQLiteDriver],
                'abstract sqlite driver wrapped in logging middleware' => [
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
