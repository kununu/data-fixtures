<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Exception;

use RuntimeException;

final class LoadFailedException extends RuntimeException
{
    private const MESSAGE = <<<'TEXT'
Failed to load fixture class "%s"

Errors:
%s
TEXT;

    public function __construct(string $fixtureClass, array $errors)
    {
        parent::__construct(sprintf(self::MESSAGE, $fixtureClass, json_encode($errors, JSON_PRETTY_PRINT)), 500);
    }
}
