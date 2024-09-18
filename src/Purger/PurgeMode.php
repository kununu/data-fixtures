<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

enum PurgeMode: int
{
    case Delete = 1;
    case Truncate = 2;

    public function equals(mixed $other): bool
    {
        return $other instanceof self && $other === $this;
    }
}
