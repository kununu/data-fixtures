<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Kununu\DataFixtures\Tools\ConnectionToolsTrait;
use Kununu\DataFixtures\Tools\DoctrineDbal\Version;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionToolsTest extends TestCase
{
    use ConnectionToolsTrait;

    private const string MY_SQL_FK_DISABLE = 'SET FOREIGN_KEY_CHECKS = 0';
    private const string MY_SQL_FK_ENABLE = 'SET FOREIGN_KEY_CHECKS = 1';
    private const string SQLITE_FK_DISABLE = 'PRAGMA foreign_keys = OFF';
    private const string SQLITE_FK_ENABLE = 'PRAGMA foreign_keys = ON';

    private MockObject&Connection $connection;

    #[DataProvider('disableForeignKeysChecksDataProvider')]
    public function testDisableForeignKeysChecks(AbstractPlatform|string $platform, string $expectedQuery): void
    {
        $this->configureExpectations($platform, $expectedQuery);

        $this->disableForeignKeysChecks($this->connection);
    }

    public static function disableForeignKeysChecksDataProvider(): array
    {
        return [
            'mysql'   => [
                new MySQLPlatform(),
                self::MY_SQL_FK_DISABLE,
            ],
            'sqlite'  => [
                Version::getSQLitePlatformClass(),
                self::SQLITE_FK_DISABLE,
            ],
            'unknown' => [
                AbstractPlatform::class,
                '',
            ],
        ];
    }

    #[DataProvider('enableForeignKeysChecksDataProvider')]
    public function testEnableForeignKeysChecks(AbstractPlatform|string $platform, string $expectedQuery): void
    {
        $this->configureExpectations($platform, $expectedQuery);

        $this->enableForeignKeysChecks($this->connection);
    }

    public static function enableForeignKeysChecksDataProvider(): array
    {
        return [
            'mysql'   => [
                new MySQLPlatform(),
                self::MY_SQL_FK_ENABLE,
            ],
            'sqlite'  => [
                Version::getSQLitePlatformClass(),
                self::SQLITE_FK_ENABLE,
            ],
            'unknown' => [
                AbstractPlatform::class,
                '',
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }

    private function configureExpectations(AbstractPlatform|string $platform, string $expectedQuery): void
    {
        $this->connection
            ->expects($this->any())
            ->method('getDatabasePlatform')
            ->willReturn($this->getPlatform($platform));

        $this->connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with($expectedQuery);
    }

    private function getPlatform(AbstractPlatform|string $platform): MockObject|AbstractPlatform
    {
        return match (true) {
            $platform instanceof AbstractPlatform => $platform,
            default                               => $this->createMock($platform),
        };
    }
}
