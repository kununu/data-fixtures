<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

final class HttpClientFixture1 extends HttpClientPhpArrayFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Http/fixture1.php',
            __DIR__ . '/Http/fixture2.nonPhp',
        ];
    }
}
