<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kununu\DataFixtures\Purger\ConnectionPurger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConnectionPurgerTest extends TestCase
{
    public function testThatPurgerByDefaultIsTransactional() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(2))
            ->method('exec')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1']);

        $connection
            ->expects($this->once())
            ->method('beginTransaction');

        $connection
            ->expects($this->once())
            ->method('commit');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatWhenPurgerIsTransactionalThenItCommits() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(2))
            ->method('exec')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1']);

        $connection
            ->expects($this->once())
            ->method('beginTransaction');

        $connection
            ->expects($this->once())
            ->method('commit');

        $purger = new ConnectionPurger($connection);
        $purger->enableTransactional();
        $purger->purge();
    }

    public function testThatWhenPurgerIsTransactionalAndAnExceptionIsThrownThenItRollbacks() : void
    {
        $this->expectException(\Exception::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(2))
            ->method('exec')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0'], ['SET FOREIGN_KEY_CHECKS=1']);

        $connection
            ->expects($this->any())
            ->method('executeUpdate')
            ->willThrowException(new \Exception());

        $connection
            ->expects($this->once())
            ->method('beginTransaction');

        $connection
            ->expects($this->once())
            ->method('rollback');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatWhenPurgerIsNotTransactionalThenItDoesNotCommits() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->never())
            ->method('beginTransaction');

        $connection
            ->expects($this->never())
            ->method('commit');

        $purger = new ConnectionPurger($connection);
        $purger->disableTransactional();
        $purger->purge();
    }

    public function testThatWhenNoTablesAreProvidedNothingIsPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock([]);

        $connection
            ->expects($this->never())
            ->method('executeUpdate');

        $purger = new ConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatExcludedTablesAreNotPurged(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(2))
            ->method('quoteIdentifier')
            ->withConsecutive(['table_1'], ['table_3'])
            ->willReturnOnConsecutiveCalls('table_1', 'table_3');

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
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->withConsecutive(['table_1'], ['table_2'], ['table_3'])
            ->willReturnOnConsecutiveCalls('table_1', 'table_2', 'table_3');

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
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->withConsecutive(['table_1'], ['table_2'], ['table_3'])
            ->willReturnOnConsecutiveCalls('table_1', 'table_2', 'table_3');

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
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(1);

        $this->assertEquals(1, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToTruncate() : void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);

        $purger->setPurgeMode(2);

        $this->assertEquals(2, $purger->getPurgeMode());
    }

    public function testChangePurgeModeToNotSupportedModeThrowsException() : void
    {
        $this->expectException(\Exception::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $purger = new ConnectionPurger($connection);
        $purger->setPurgeMode(10);
    }

    private function getConnectionMock(array $tables = ['table_1', 'table_2', 'table_3']) : MockObject
    {
        $connection = $this->createMock(Connection::class);
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->expects($this->any())->method('listTableNames')->willReturn($tables);
        $connection->expects($this->any())->method('getSchemaManager')->willReturn($schemaManager);

        return $connection;
    }
}
