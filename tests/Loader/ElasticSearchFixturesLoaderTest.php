<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Loader\ElasticSearchFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ElasticSearchFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\NotAFixture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchFixturesLoaderTest extends TestCase
{
    /** @var ElasticSearchFixturesLoader */
    private $loader;

    public function testLoadFromDirectory(): void
    {
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock1')
        );
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock2')
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__ . '/../TestFixtures/');
        $this->assertCount(5, $this->loader->getFixtures());
    }

    public function testLoadFromDirectoryThrowsExceptionIfNotDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromDirectory(__DIR__ . '/../NotFoundDirectory/');
    }

    public function testLoadFromFile(): void
    {
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock1')
        );
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock2')
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ElasticSearchFixture1.php');
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/NotAFixture.php');
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ElasticSearchFixture2.php');
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testLoadFromFileThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromFile(__DIR__ . '/../NotFoundDirectory/ElasticSearchFixture1.php');
    }

    public function testLoadFromClassName(): void
    {
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock1')
        );
        $this->loader->addFixture(
            $this->getNamedElasticSearchFixtureMock('Mock2')
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromClassName(ElasticSearchFixture1::class);
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromClassName(NotAFixture::class);
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromClassName(ElasticSearchFixture2::class);
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testGetFixture(): void
    {
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ElasticSearchFixture1.php');

        $fixture = $this->loader->getFixture(ElasticSearchFixture1::class);

        $this->assertInstanceOf(ElasticSearchFixture1::class, $fixture);
    }

    public function testGetFixtureThrowsExceptionWhenFixtureDoesNotExists(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->getFixture(ElasticSearchFixture1::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ElasticSearchFixturesLoader();
    }

    private function getNamedElasticSearchFixtureMock(string $name): MockObject
    {
        return $this->getMockBuilder(ElasticSearchFixtureInterface::class)
            ->setMockClassName($name)
            ->getMock();
    }
}
