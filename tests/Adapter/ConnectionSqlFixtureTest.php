<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter;

use Doctrine\DBAL\Connection;
use Kununu\DataFixtures\Exception\InvalidFileException;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionSqlFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\InvalidConnectionSqlFixture;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionSqlFixtureTest extends TestCase
{
    private MockObject&Connection $connection;

    public function testLoad(): void
    {
        $fixture1Content = <<<'SQL'
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('1', 'name', 'description;');
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('2', 'name2', 'description2\n');
SQL;

        $fixture2Content = <<<'SQL'
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('3', 'name3', 'description3');
INSERT INTO `database`.`table` (`id`, `name`, `description`) VALUES ('4', 'name4', 'description4');
SQL;

        $this->connection
            ->expects(self::exactly(2))
            ->method('executeStatement')
            ->willReturnCallback(fn(string $fixtureContent) => match ($fixtureContent) {
                $fixture1Content,
                $fixture2Content => 1,
                default          => throw new LogicException(sprintf('Unknown fixture content "%s"', $fixtureContent)),
            });

        $fixture = new ConnectionSqlFixture1();
        $fixture->load($this->connection);
    }

    public function testThatLoadThrowsExceptionWhenCannotGetContentsOfFile(): void
    {
        $this->expectException(InvalidFileException::class);

        $fixture = new InvalidConnectionSqlFixture();
        $fixture->load($this->connection);
    }

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }
}
