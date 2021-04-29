<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Executor;

interface ExecutorInterface
{
    public function execute(array $fixtures, $append = false): void;
}
