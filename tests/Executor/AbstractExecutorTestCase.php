<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractExecutorTestCase extends TestCase
{
    protected MockObject&PurgerInterface $purger;
    protected ExecutorInterface $executor;

    abstract protected function getExecutor(): ExecutorInterface;

    protected function setUp(): void
    {
        $this->purger = $this->createMock(PurgerInterface::class);
        $this->executor = $this->getExecutor();
    }
}
