<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use InvalidArgumentException;
use Kununu\DataFixtures\Tests\TestFixtures\InvalidLoadModeFixture;
use PHPUnit\Framework\TestCase;

final class InvalidLoadModeFixtureTest extends TestCase
{
    public function testFixture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid load mode: "invalid load mode"');

        (new InvalidLoadModeFixture())->load();
    }
}
