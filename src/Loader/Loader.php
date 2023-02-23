<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Loader;

use Kununu\DataFixtures\FixtureInterface;
use Kununu\DataFixtures\InitializableFixtureInterface;

abstract class Loader implements LoaderInterface
{
    private const FILE_EXTENSION = '.php';

    private array $fixtures = [];
    private array $initalizableFixtures = [];

    final public function loadFromDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist', $dir));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $this->loadFromIterator($iterator);
    }

    final public function loadFromFile(string $fileName): void
    {
        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist or is not readable', $fileName));
        }

        $this->loadFromIterator(new \ArrayIterator([new \SplFileInfo($fileName)]));
    }

    final public function loadFromClassName(string $className): void
    {
        $reflClass = new \ReflectionClass($className);
        $this->loadFromIterator(new \ArrayIterator([new \SplFileInfo($reflClass->getFileName())]));
    }

    final public function getFixture(string $className): FixtureInterface
    {
        if (!isset($this->fixtures[$className])) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a registered fixture', $className));
        }

        return $this->fixtures[$className];
    }

    final public function addFixture(FixtureInterface $fixture): void
    {
        if (!isset($this->fixtures[$fixtureClass = $fixture::class])) {
            $this->fixtures[$fixtureClass] = $fixture;
        }
    }

    final public function getFixtures(): array
    {
        return $this->fixtures;
    }

    final public function registerInitializableFixture(string $className, mixed ...$args): void
    {
        if (!isset($this->initalizableFixtures[$className])) {
            $this->initalizableFixtures[$className] = $args;
        }
    }

    final public function clearFixtures(): void
    {
        $this->fixtures = [];
    }

    abstract protected function supports(string $className): bool;

    private function createFixture(string $className): FixtureInterface
    {
        $class = new $className();

        if (isset($this->initalizableFixtures[$className])
            && in_array(InitializableFixtureInterface::class, class_implements($class))
        ) {
            $class->initializeFixture(...$this->initalizableFixtures[$className]);
        }

        return $class;
    }

    private function loadFromIterator(\Iterator $iterator): void
    {
        $includedFiles = [];
        foreach ($iterator as $file) {
            if ($file->getBasename(self::FILE_EXTENSION) == $file->getBasename()) {
                continue;
            }
            $sourceFile = realpath($file->getPathName());
            require_once $sourceFile;
            $includedFiles[] = $sourceFile;
        }

        $declared = get_declared_classes();

        // Make the declared classes order deterministic
        sort($declared);

        foreach ($declared as $className) {
            $reflClass = new \ReflectionClass($className);
            $sourceFile = $reflClass->getFileName();

            if (in_array($sourceFile, $includedFiles) && $this->supports($className)) {
                $fixture = $this->createFixture($className);
                $this->addFixture($fixture);
            }
        }
    }
}
