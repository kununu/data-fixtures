<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\TestFixtures;

use Kununu\DataFixtures\Adapter\HttpClientPhpArrayFixture;

final class HttpClientFixture2 extends HttpClientPhpArrayFixture
{
    protected function fileNames(): array
    {
        return [
            __DIR__ . '/Responses/badfile.php',
        ];
    }
}
