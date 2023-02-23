<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractPurgerTestCase extends TestCase
{
    protected PurgerInterface $purger;

    abstract protected function getPurger(): PurgerInterface;

    protected function setUp(): void
    {
        $this->purger = $this->getPurger();
    }
}
