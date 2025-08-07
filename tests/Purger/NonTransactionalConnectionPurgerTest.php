<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Exception;
use Kununu\DataFixtures\Purger\NonTransactionalConnectionPurger;

final class NonTransactionalConnectionPurgerTest extends AbstractConnectionPurgerTestCase
{
    public function testThatPurgerIsNotTransactionalAndCommits(): void
    {
        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(5))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(itemsAsArray: false)
                    )
                )
            )
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

        $connection = $this->getConnectionMock();

        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->with(
                $this->callback(
                    fn(string $statement): bool => in_array(
                        $statement,
                        $this->getConsecutiveArgumentsForConnectionExecStatement(
                            tables: [self::TABLE_1],
                            itemsAsArray: false
                        )
                    )
                )
            )
            ->willReturnCallback(
                fn(string $sql): int => match (true) {
                    'DELETE FROM `table_1`' === $sql => throw new Exception(),
                    default                          => 1,
                }
            );

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
