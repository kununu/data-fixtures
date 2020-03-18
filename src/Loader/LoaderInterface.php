<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\FixtureInterface;

interface LoaderInterface
{
    public function loadFromDirectory(string $dir) : void;

    public function loadFromFile(string $fileName) : void;

    public function loadFromClassName(string $className) : void;

    public function getFixture(string $className) : FixtureInterface;

    public function addFixture(FixtureInterface $fixture) : void;

    public function getFixtures() : array;

    public function registerInitializableFixture(string $className, ...$args): void;
}
