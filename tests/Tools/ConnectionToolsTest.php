<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    #[DataProvider('mysqlDataProvider')]
    public function testGetDisableForeignKeyChecksForMySQL(AbstractPlatform $platform): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=0',
            $this->getDisableForeignKeysChecksStatementByPlatform($platform),
        );
    }

    #[DataProvider('mysqlDataProvider')]
    public function testGetEnableForeignKeyChecksForMySQL(AbstractPlatform $platform): void
    {
        self::assertEquals(
            'SET FOREIGN_KEY_CHECKS=1',
            $this->getEnableForeignKeysChecksStatementByPlatform($platform),
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetDisableForeignKeyChecksForSqlite(AbstractPlatform $platform): void
    {
        self::assertEquals(
            'PRAGMA foreign_keys = OFF',
            $this->getDisableForeignKeysChecksStatementByPlatform($platform)
        );
    }

    #[DataProvider('sqliteDataProvider')]
    public function testGetEnableForeignKeyChecksForSqlite(AbstractPlatform $platform): void
    {
        self::assertEquals(
            'PRAGMA foreign_keys = ON',
            $this->getEnableForeignKeysChecksStatementByPlatform($platform)
        );
    }

    public function testGetEnableForeignKeyChecksForUnknownDriver(): void
    {
        self::assertEquals(
            '',
            $this->getEnableForeignKeysChecksStatementByPlatform(self::createStub(AbstractPlatform::class))
        );
    }

    public function testGetDisableForeignKeyChecksForUnknownDriver(): void
    {
        self::assertEquals(
            '',
            $this->getDisableForeignKeysChecksStatementByPlatform(self::createStub(AbstractPlatform::class))
        );
    }

    /** @return array<string, array{AbstractPlatform}> */
    public static function mysqlDataProvider(): array
    {
        return [
            'abstract_mysql' => [self::createStub(AbstractMySQLPlatform::class)],
            'mysql'          => [self::createStub(MySQLPlatform::class)],
        ];
    }

    /** @return array<string, array{AbstractPlatform}> */
    public static function sqliteDataProvider(): array
    {
        // Using FQCNs here instead of importing them as aliases because CS Fixer wreak havoc with those.
        if (class_exists(\Doctrine\DBAL\Platforms\SQLitePlatform::class)) {
            return [
                'sqlite_dbal_4' => [self::createStub(\Doctrine\DBAL\Platforms\SQLitePlatform::class)],
            ];
        }

        if (class_exists(\Doctrine\DBAL\Platforms\SqlitePlatform::class)) {
            return [
                'sqlite_dbal_3' => [self::createStub(\Doctrine\DBAL\Platforms\SqlitePlatform::class)],
            ];
        }

        return [];
    }
}
