<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\ConnectionFixtureInterface;
use Kununu\DataFixtures\Loader\ConnectionFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\ConnectionFixture1;
use PHPUnit\Framework\TestCase;

final class SqlFixturesLoaderTest extends TestCase
{
    /** @var ConnectionFixturesLoader */
    private $loader;

    public function testLoadFromDirectory()
    {
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__. '/../TestFixtures/');
        $this->assertCount(5, $this->loader->getFixtures());
    }

    public function testLoadFromFile()
    {
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(ConnectionFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/ConnectionFixture1.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/NotAFixture.php');
        $this->assertCount(3, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/ConnectionFixture2.php');
        $this->assertCount(4, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/ConnectionFixture3.php');
        $this->assertCount(5, $this->loader->getFixtures());
    }

    public function testGetFixture()
    {
        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/ConnectionFixture1.php');

        $fixture = $this->loader->getFixture(ConnectionFixture1::class);

        $this->assertInstanceOf(ConnectionFixture1::class, $fixture);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new ConnectionFixturesLoader();
    }
}
