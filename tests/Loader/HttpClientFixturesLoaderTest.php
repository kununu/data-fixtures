<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use InvalidArgumentException;
use Kununu\DataFixtures\Adapter\HttpClientFixtureInterface;
use Kununu\DataFixtures\Loader\HttpClientFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\HttpClientFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\NotAFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HttpClientFixturesLoaderTest extends TestCase
{
    private $loader;

    public function testLoadFromDirectory(): void
    {
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock2'));

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__ . '/../TestFixtures/');
        $this->assertCount(6, $this->loader->getFixtures());

        $this->loader->clearFixtures();
        $this->assertEmpty($this->loader->getFixtures());
    }

    public function testLoadFromDirectoryThrowsExceptionIfNotDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->loadFromDirectory(__DIR__ . '/../NotFoundDirectory/');
    }

    public function testLoadFromFile(): void
    {
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock2'));

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/HttpClientFixture1.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/NotAFixture.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/HttpClientFixture2.php');
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testLoadFromFileThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->loadFromFile(__DIR__ . '/../NotFoundDirectory/HttpClientFixture1.php');
    }

    public function testLoadFromClassName(): void
    {
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock1'));
        $this->loader->addFixture($this->getNamedHttpClientFixtureMock('Mock2'));

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromClassName(HttpClientFixture1::class);
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromClassName(NotAFixture::class);
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromClassName(HttpClientFixture2::class);
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testGetFixture(): void
    {
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/HttpClientFixture1.php');

        $fixture = $this->loader->getFixture(HttpClientFixture1::class);

        $this->assertInstanceOf(HttpClientFixture1::class, $fixture);
    }

    public function testGetFixtureThrowsExceptionWhenFixtureDoesNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->getFixture(HttpClientFixture1::class);
    }

    protected function setUp(): void
    {
        $this->loader = new HttpClientFixturesLoader();
    }

    /**
     * @param string $name
     *
     * @return HttpClientFixtureInterface|MockObject
     */
    private function getNamedHttpClientFixtureMock(string $name)
    {
        return $this->getMockBuilder(HttpClientFixtureInterface::class)
            ->setMockClassName($name)
            ->getMock();
    }
}
