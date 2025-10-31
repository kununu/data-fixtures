<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Tools\DoctrineDbal;

use Kununu\DataFixtures\Tools\DoctrineDbal\DbalVersion;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class DbalVersionTest extends TestCase
{
    public function testCases(): void
    {
        self::assertEquals(
            [
                DbalVersion::Version3,
                DbalVersion::Version4,
            ],
            DbalVersion::cases()
        );
    }

    #[TestWith([DbalVersion::Version3, DbalVersion::Version3, true], 'version_3_equals_version_3')]
    #[TestWith([DbalVersion::Version3, DbalVersion::Version4, false], 'version_3_not_equals_version_4')]
    #[TestWith([DbalVersion::Version4, DbalVersion::Version3, false], 'version_4_not_equals_version_3')]
    #[TestWith([DbalVersion::Version4, DbalVersion::Version4, true], 'version_4_equals_version_4')]
    public function testEquals(DbalVersion $dbalVersion, DbalVersion $other, bool $expected): void
    {
        self::assertEquals($expected, $dbalVersion->equals($other));
    }

    #[TestWith([DbalVersion::Version3, true], 'is_version_3')]
    #[TestWith([DbalVersion::Version4, false], 'is_not_version_3')]
    #[TestDox('Is DBAL3 with $_dataName')]
    public function testIsDBAL3(DbalVersion $dbalVersion, bool $expected): void
    {
        self::assertEquals($expected, $dbalVersion->isDBAL3());
    }

    #[TestWith([DbalVersion::Version4, true], 'is_version_4')]
    #[TestWith([DbalVersion::Version3, false], 'is_not_version_4')]
    #[TestDox('Is DBAL4 with $_dataName')]
    public function testIsDBAL4(DbalVersion $dbalVersion, bool $expected): void
    {
        self::assertEquals($expected, $dbalVersion->isDBAL4());
    }
}
