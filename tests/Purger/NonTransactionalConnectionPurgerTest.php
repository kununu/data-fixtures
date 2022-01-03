<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Doctrine\DBAL\Connection;
use Exception;
use Kununu\DataFixtures\Purger\NonTransactionalConnectionPurger;
use PHPUnit\Framework\MockObject\MockObject;

final class NonTransactionalConnectionPurgerTest extends ConnectionPurgerTestCase
{
    public function testThatPurgerIsNotTransactionalAndCommits(): void
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement())
            ->willReturn(1);

        $connection
            ->expects($this->never())
            ->method('beginTransaction');

        $connection
            ->expects($this->never())
            ->method('commit');

        $purger = new NonTransactionalConnectionPurger($connection);
        $purger->purge();
    }

    public function testThatPurgerIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);

        /** @var MockObject|Connection $connection */
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(3))
            ->method($this->getExecuteQueryMethodName($connection))
            ->withConsecutive(...$this->getConsecutiveArgumentsForConnectionExecStatement(1, ['table_1']))
            ->willReturnCallback(function(string $sql): int {
                if ('DELETE FROM table_1' === $sql) {
                    throw new Exception();
                }

                return 1;
            });

        $connection
            ->expects($this->never())
            ->method('beginTransaction');

        $connection
            ->expects($this->never())
            ->method('commit');

        $connection
            ->expects($this->never())
            ->method('rollback');

        $purger = new NonTransactionalConnectionPurger($connection);
        $purger->purge();
    }
}
