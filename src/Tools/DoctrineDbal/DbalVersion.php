<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tools\DoctrineDbal;

enum DbalVersion: int
{
    case Version3 = 3;
    case Version4 = 4;

    public function equals(self $other): bool
    {
        return $this === $other;
    }

    public function isDBAL3(): bool
    {
        return $this->equals(self::Version3);
    }

    public function isDBAL4(): bool
    {
        return $this->equals(self::Version4);
    }
}
