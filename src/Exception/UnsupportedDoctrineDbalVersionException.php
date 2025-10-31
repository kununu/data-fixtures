<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Exception;

use RuntimeException;

final class UnsupportedDoctrineDbalVersionException extends RuntimeException
{
    public function __construct(string $version)
    {
        parent::__construct(sprintf('Unsupported "doctrine/dbal" version: %s', $version));
    }
}
