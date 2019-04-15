<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger\Cache;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionPurgerTest extends TestCase
{
    public function testThatWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn([]);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $connection
            ->expects($this->never())
            ->method('executeUpdate');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $connection
            ->expects($this->exactly(2))
            ->method('executeUpdate')
            ->withConsecutive(
                ['DELETE FROM table_1'],
                ['DELETE FROM table_3']
            );

        $purger = new ConnectionPurger(
            $connection,
            ['table_4', 'table_2', 'table_5']
        );

        $purger->purge();
    }

    public function testThatPurgesWithDeleteMode(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($this->createMock(AbstractPlatform::class));

        $connection
            ->expects($this->exactly(3))
            ->method('executeUpdate')
            ->withConsecutive(
                ['DELETE FROM table_1'],
                ['DELETE FROM table_2'],
                ['DELETE FROM table_3']
            );

        $purger = new ConnectionPurger($connection);

        $purger->purge();
    }

    public function testThatPurgesWithTruncateMode(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

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

        $connection
            ->expects($this->once())
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $connection
            ->expects($this->exactly(3))
            ->method('executeUpdate')
            ->withConsecutive(
                ['TRUNCATE table_1'],
                ['TRUNCATE table_2'],
                ['TRUNCATE table_3']
            );

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);
        $purger->purge();
    }

    public function testChangePurgeModeToDelete() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(1);

        $this->assertEquals(1, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToTruncate() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);

        $this->assertEquals(2, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToNotSupportedModeThrowsException() : void
    {
        $this->expectException(\Exception::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn(['table_1', 'table_2', 'table_3']);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        $purger = new ConnectionPurger($connection);
        $purger->setPurgeMode(10);
    }
}
