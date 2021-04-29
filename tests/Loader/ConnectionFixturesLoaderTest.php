<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture3;
use Kununu\DataFixtures\Tests\TestFixtures\NotAFixture;
use PHPUnit\Framework\TestCase;

final class ConnectionFixturesLoaderTest extends TestCase
{
    /** @var ConnectionFixturesLoader */
    private $loader;

    public function testLoadFromDirectory(): void
    {
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__ . '/../TestFixtures/');
        $this->assertCount(6, $this->loader->getFixtures());

        $this->loader->clearFixtures();
        $this->assertEmpty($this->loader->getFixtures());
    }

    public function testLoadFromDirectoryThrowsExceptionIfNotDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromDirectory(__DIR__ . '/../NotFoundDirectory/');
    }

    public function testLoadFromFile(): void
    {
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ConnectionFixture1.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/NotAFixture.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ConnectionFixture2.php');
        $this->assertCount(4, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ConnectionFixture3.php');
        $this->assertCount(5, $this->loader->getFixtures());
    }

    public function testLoadFromFileThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromFile(__DIR__ . '/../NotFoundDirectory/CachePoolFixture1.php');
    }

    public function testLoadFromClassName(): void
    {
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromClassName(ConnectionFixture1::class);
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromClassName(NotAFixture::class);
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromClassName(ConnectionFixture2::class);
        $this->assertCount(4, $this->loader->getFixtures());

        $this->loader->loadFromClassName(ConnectionFixture3::class);
        $this->assertCount(5, $this->loader->getFixtures());
    }

    public function testGetFixture(): void
    {
        $this->loader->loadFromFile(__DIR__ . '/../TestFixtures/ConnectionFixture1.php');

        $fixture = $this->loader->getFixture(ConnectionFixture1::class);

        $this->assertInstanceOf(ConnectionFixture1::class, $fixture);
    }

    public function testGetFixtureThrowsExceptionWhenFixtureDoesNotExists(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->getFixture(ConnectionFixture1::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ConnectionFixturesLoader();
    }
}
