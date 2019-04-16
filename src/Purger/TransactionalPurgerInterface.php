<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

interface TransactionalPurgerInterface extends PurgerInterface
{
    public function enableTransactional() : void;

    public function disableTransactional() : void;
}
