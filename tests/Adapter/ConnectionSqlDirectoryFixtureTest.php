<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionSqlDirectoryFixture1;
use Kununu\DataFixtures\Tests\Utils\ConnectionUtilsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionSqlDirectoryFixtureTest extends TestCase
{
    use ConnectionUtilsTrait;

    public function testLoad(): void
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->createMock(Connection::class);

        $fixture1Content = <<<'SQL'
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('1', 'name', 'description;');
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('2', 'name2', 'description2\n');
SQL;

        $fixture2Content = <<<'SQL'
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('3', 'name3', 'description3');
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('4', 'name4', 'description4');
SQL;

        $connection
            ->expects($this->exactly(2))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(
                [$fixture1Content],
                [$fixture2Content]
            )
            ->willReturn(1);

        $fixture = new ConnectionSqlDirectoryFixture1();
        $fixture->load($connection);
    }
}
