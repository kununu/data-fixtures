<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @internal
 */
trait ConnectionToolsTrait
{
    protected function getDisableForeignKeysChecksStatementByPlatform(AbstractPlatform $platform): string
    {
        return match (true) {
            self::isMySQLPlatform($platform)  => 'SET FOREIGN_KEY_CHECKS=0',
            self::isSQLitePlatform($platform) => 'PRAGMA foreign_keys = OFF',
            default                           => '',
        };
    }

    protected function getEnableForeignKeysChecksStatementByPlatform(AbstractPlatform $platform): string
    {
        return match (true) {
            self::isMySQLPlatform($platform)  => 'SET FOREIGN_KEY_CHECKS=1',
            self::isSQLitePlatform($platform) => 'PRAGMA foreign_keys = ON',
            default                           => '',
        };
    }

    private static function isMySQLPlatform(AbstractPlatform $platform): bool
    {
        return $platform instanceof AbstractMySQLPlatform;
    }

    private static function isSQLitePlatform(AbstractPlatform $platform): bool
    {
        // Using FQCNs here instead of importing them as aliases because CS Fixer wreak havoc with those.
        return
            (
                class_exists(\Doctrine\DBAL\Platforms\SQLitePlatform::class)
                && ($platform instanceof \Doctrine\DBAL\Platforms\SQLitePlatform)
            )
            || (
                class_exists(\Doctrine\DBAL\Platforms\SqlitePlatform::class)
                && ($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform)
            )
        ;
    }
}
