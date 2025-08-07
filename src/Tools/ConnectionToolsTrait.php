<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;

trait ConnectionToolsTrait
{
    protected function getDisableForeignKeysChecksStatementByPlatform(AbstractPlatform $platform): string
    {
        return match (true) {
            $platform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=0',
            $platform instanceof SQLitePlatform        => 'PRAGMA foreign_keys = OFF',
            default                                    => '',
        };
    }

    protected function getEnableForeignKeysChecksStatementByPlatform(AbstractPlatform $platform): string
    {
        return match (true) {
            $platform instanceof AbstractMySQLPlatform => 'SET FOREIGN_KEY_CHECKS=1',
            $platform instanceof SQLitePlatform        => 'PRAGMA foreign_keys = ON',
            default                                    => '',
        };
    }
}
