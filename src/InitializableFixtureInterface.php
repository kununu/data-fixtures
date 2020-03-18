<?php declare(strict_types=1);

namespace Kununu\DataFixtures;

interface InitializableFixtureInterface
{
    public function initializeFixture(...$args): void;
}
