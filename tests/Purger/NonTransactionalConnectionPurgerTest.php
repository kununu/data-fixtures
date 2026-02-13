<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Exception;
use Kununu\DataFixtures\Purger\NonTransactionalConnectionPurger;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class NonTransactionalConnectionPurgerTest extends AbstractConnectionPurgerTestCase
{
    public function testThatPurgerIsNotTransactionalAndCommits(): void
    {
        $this->configureTables();
        $this->configureConnectionToNonTransactional();

        $this->connection
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

        $this->purge();
    }

    public function testThatPurgerIsTransactionalAndRollbacks(): void
    {
        $this->expectException(Exception::class);
        $this->configureTables();
        $this->configureConnectionToNonTransactional();

        $this->connection
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
                static fn(string $sql): int => match (true) {
                    'DELETE FROM `table_1`' === $sql => throw new Exception(),
                    default                          => 1,
                }
            );

        $this->purge();
    }

    protected function configureConnectionToNonTransactional(): void
    {
        $this->connection
            ->expects($this->never())
            ->method('beginTransaction');

        $this->connection
            ->expects($this->never())
            ->method('commit');

        $this->connection
            ->expects($this->never())
            ->method('rollback');
    }

    private function purge(): void
    {
        new NonTransactionalConnectionPurger($this->connection)->purge();
    }
}
