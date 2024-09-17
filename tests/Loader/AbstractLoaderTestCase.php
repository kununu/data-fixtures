<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use DateTime;
use InvalidArgumentException;
use Kununu\DataFixtures\FixtureInterface;
use Kununu\DataFixtures\Loader\LoaderInterface;
use Kununu\DataFixtures\Tests\TestFixtures\NotAFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractLoaderTestCase extends TestCase
{
    protected LoaderInterface $loader;

    private array $fixtureClasses = [];
    private array $fixtureFiles = [];

    public function testGetFixture(): void
    {
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/' . $this->fixtureFiles[0]);

        $fixture = $this->loader->getFixture($fixtureClass = $this->fixtureClasses[0]);

        self::assertInstanceOf($fixtureClass, $fixture);
    }

    public function testGetFixtureThrowsExceptionWhenFixtureDoesNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->getFixture($this->fixtureClasses[0]);
    }

    public function testLoadFromClassName(): void
    {
        $this->loader->addFixture($this->getNamedFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedFixtureMock('Mock2'));

        self::assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromClassName(NotAFixture::class);

        self::assertCount(2, $this->loader->getFixtures());

        $count = 2;
        foreach ($this->fixtureClasses as $fixtureClass) {
            $this->loader->loadFromClassName($fixtureClass);
            ++$count;

            self::assertCount($count, $this->loader->getFixtures());
        }
    }

    public function testLoadFromDirectory(): void
    {
        $this->loader->addFixture($this->getNamedFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedFixtureMock('Mock2'));

        self::assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__ . '/../TestFixtures/');

        self::assertCount($this->expectedNumberOfFixturesFromDirectory(), $this->loader->getFixtures());

        $this->loader->clearFixtures();

        self::assertEmpty($this->loader->getFixtures());
    }

    public function testLoadFromDirectoryThrowsExceptionIfNotDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->loadFromDirectory(__DIR__ . '/../NotFoundDirectory/');
    }

    public function testLoadFromFile(): void
    {
        $this->loader->addFixture($this->getNamedFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedFixtureMock('Mock2'));

        self::assertCount(2, $this->loader->getFixtures());

        $this->initializeLoadFromFileFixtures();

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/NotAFixture.php');

        self::assertCount(2, $this->loader->getFixtures());

        $count = 2;
        foreach ($this->fixtureFiles as $fixtureFile) {
            $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/' . $fixtureFile);
            ++$count;

            self::assertCount($count, $this->loader->getFixtures());
        }

        $this->performExtraLoadFromFileFixturesAssertions();
    }

    public function testLoadFromFileThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->loadFromFile(__DIR__ . '/../NotFoundDirectory/ThisDoesNotExists.php');
    }

    abstract protected function getLoader(): LoaderInterface;

    abstract protected function getFixtureInterfaceName(): string;

    abstract protected function getFixtureClasses(): array;

    abstract protected function expectedNumberOfFixturesFromDirectory(): int;

    protected function setUp(): void
    {
        $this->loader = $this->getLoader();
        $this->fixtureClasses = array_keys($this->getFixtureClasses());
        $this->fixtureFiles = array_values($this->getFixtureClasses());
    }

    protected function initializeLoadFromFileFixtures(): void
    {
    }

    protected function performExtraLoadFromFileFixturesAssertions(): void
    {
    }

    protected function getNamedFixtureMock(string $name): MockObject|FixtureInterface
    {
        return $this->getMockBuilder($this->getFixtureInterfaceName())
            ->setMockClassName($this->generateMockClassName($name))
            ->getMock();
    }

    private function generateMockClassName(string $prefix): string
    {
        return sprintf('%s%s', $prefix, md5((new DateTime())->format('Y-m-d H:i:s.uP')));
    }
}
