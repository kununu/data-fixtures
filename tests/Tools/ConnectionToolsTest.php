<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use PHPUnit\Framework\TestCase;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    public function testGetDisableForeignKeyChecksForMySQL(): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByDriver($this->createMock(AbstractMySQLDriver::class))
        );
    }

    public function testGetEnableForeignKeyChecksForMySQL(): void
    {
        $this->assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByDriver($this->createMock(AbstractMySQLDriver::class))
        );
    }

    public function testGetDisableForeignKeyChecksForSqlite(): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByDriver($this->createMock(AbstractSQLiteDriver::class))
        );
    }

    public function testGetEnableForeignKeyChecksForSqlite(): void
    {
        $this->assertEquals(
            'PRAGMA foreign_keys = ON',
            $this->getEnableForeignKeysChecksStatementByDriver($this->createMock(AbstractSQLiteDriver::class))
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
}
