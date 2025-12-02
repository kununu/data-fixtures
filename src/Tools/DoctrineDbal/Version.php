<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools\DoctrineDbal;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Kununu\DataFixtures\Exception\UnsupportedDoctrineDbalVersionException;

final class Version
{
    private const string PACKAGE = 'doctrine/dbal';

    private static ?DbalVersion $dbalVersion = null;

    public static function version(): DbalVersion
    {
        return self::$dbalVersion ?? self::$dbalVersion = match (true) {
            InstalledVersions::satisfies(new VersionParser(), self::PACKAGE, '^3.0') => DbalVersion::Version3,
            InstalledVersions::satisfies(new VersionParser(), self::PACKAGE, '^4.0') => DbalVersion::Version4,
            default                                                                  => self::unsupported(),
        };
    }

    public static function getSQLitePlatformClass(): string
    {
        return match (self::version()) {
            DbalVersion::Version3 => 'Doctrine\DBAL\Platforms\SqlitePlatform',
            DbalVersion::Version4 => 'Doctrine\DBAL\Platforms\SQLitePlatform',
        };
    }

    private static function unsupported(): never
    {
        throw new UnsupportedDoctrineDbalVersionException(InstalledVersions::getPrettyVersion(self::PACKAGE));
    }
}

Version::__constructStatic();
