<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\FixtureInterface;

abstract class Loader implements LoaderInterface
{
    private $fixtures = [];

    private $fileExtension = '.php';

    final public function loadFromDirectory(string $dir) : array
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $dir));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        return $this->loadFromIterator($iterator);
    }

    final public function loadFromFile(string $fileName) : array
    {
        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist or is not readable', $fileName));
        }

        $iterator = new \ArrayIterator([new \SplFileInfo($fileName)]);
        return $this->loadFromIterator($iterator);
    }

    final public function getFixture(string $className) : FixtureInterface
    {
        if (!isset($this->fixtures[$className])) {
            throw new \InvalidArgumentException(sprintf(
                '"%s" is not a registered fixture',
                $className
            ));
        }

        return $this->fixtures[$className];
    }

    final public function addFixture(FixtureInterface $fixture) : void
    {
        $fixtureClass = get_class($fixture);

        if (!isset($this->fixtures[$fixtureClass])) {
            $this->fixtures[$fixtureClass] = $fixture;
        }
    }

    final public function getFixtures() : array
    {
        return $this->fixtures;
    }

    abstract protected function supports(string $className) : bool;

    private function createFixture(string $class) : FixtureInterface
    {
        return new $class();
    }

    private function loadFromIterator(\Iterator $iterator) : array
    {
        $includedFiles = [];
        foreach ($iterator as $file) {
            if (($fileName = $file->getBasename($this->fileExtension)) == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        $fixtures = [];
        $declared = get_declared_classes();

        // Make the declared classes order deterministic
        sort($declared);

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) && $this->supports($className)) {
                $fixture = $this->createFixture($className);
                $fixtures[] = $fixture;
                $this->addFixture($fixture);
            }
        }

        return $fixtures;
    }
}
