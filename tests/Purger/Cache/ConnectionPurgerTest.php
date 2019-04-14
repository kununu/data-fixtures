<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger\Cache;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionPurgerTest extends TestCase
{
    /** @var MockObject|Connection */
    private $connection;

    public function testThatWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        $this->connection
            ->expects($this->never())
            ->method($this->anything());

        $purger = new ConnectionPurger($this->connection, []);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $this->connection
            ->expects($this->exactly(2))
            ->method('executeUpdate')
            ->withConsecutive(
                ['DELETE FROM table_1'],
                ['DELETE FROM table_3']
            );

        $purger = new ConnectionPurger(
            $this->connection,
            [
                'table_1',
                'table_2',
                'table_3',
            ],
            ['table_4', 'table_2', 'table_5']
        );

        $purger->purge();
    }

    public function testThatPurgesWithDeleteMode(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $this->connection
            ->expects($this->exactly(3))
            ->method('executeUpdate')
            ->withConsecutive(
                ['DELETE FROM table_1'],
                ['DELETE FROM table_2'],
                ['DELETE FROM table_3']
            );

        $purger = new ConnectionPurger(
            $this->connection,
            [
                'table_1',
                'table_2',
                'table_3',
            ]
        );

        $purger->purge();
    }

    public function testThatPurgesWithTruncateMode(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->expects($this->exactly(3))
            ->method('getTruncateTableSQL')
            ->withConsecutive(
                ['table_1', true],
                ['table_2', true],
                ['table_3', true]
            )
            ->willReturnOnConsecutiveCalls(
                'TRUNCATE table_1',
                'TRUNCATE table_2',
                'TRUNCATE table_3'
            );

        $this->connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $this->connection
            ->expects($this->exactly(3))
            ->method('executeUpdate')
            ->withConsecutive(
                ['TRUNCATE table_1'],
                ['TRUNCATE table_2'],
                ['TRUNCATE table_3']
            );

        $purger = new ConnectionPurger(
            $this->connection,
            [
                'table_1',
                'table_2',
                'table_3',
            ]
        );

        $purger->setPurgeMode(2);
        $purger->purge();
    }

    public function testChangePurgeModeToDelete() : void
    {
        $purger = new ConnectionPurger($this->connection, []);

        $purger->setPurgeMode(1);

        $this->assertEquals(1, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToTruncate() : void
    {
        $purger = new ConnectionPurger($this->connection, []);

        $purger->setPurgeMode(2);

        $this->assertEquals(2, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToNotSupportedModeThrowsException() : void
    {
        $this->expectException(\Exception::class);

        $purger = new ConnectionPurger($this->connection, []);
        $purger->setPurgeMode(10);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createMock(Connection::class);
    }
}
