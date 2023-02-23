<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Utils;

use Doctrine\DBAL\Connection;

trait ConnectionUtilsTrait
{
    public function getExecuteQueryMethodName(Connection $connection): string
    {
        // This way we support both doctrine/dbal ^2.9 and ^3.1
        return method_exists($connection, 'executeStatement') ? 'executeStatement' : 'exec';
    }
}
