<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\FixtureInterface;

interface LoaderInterface
{
    public function loadFromDirectory(string $dir) : array;

    public function loadFromFile(string $fileName) : array;

    public function getFixture(string $className) : FixtureInterface;

    public function addFixture(FixtureInterface $fixture) : void;

    public function getFixtures() : array;
}
