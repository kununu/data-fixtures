<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

interface PurgerInterface
{
    public function purge(): void;
}
