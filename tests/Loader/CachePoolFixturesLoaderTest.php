<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\CachePoolFixture1;
use PHPUnit\Framework\TestCase;

final class CachePoolFixturesLoaderTest extends TestCase
{
    /** @var CachePoolFixturesLoader */
    private $loader;
    
    public function testLoadFromDirectory()
    {
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromDirectory(__DIR__. '/../TestFixtures/');
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testLoadFromFile()
    {
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/CachePoolFixture1.php');
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/NotAFixture.php');
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/CachePoolFixture2.php');
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testGetFixture()
    {
        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/CachePoolFixture1.php');

        $fixture = $this->loader->getFixture(CachePoolFixture1::class);

        $this->assertInstanceOf(CachePoolFixture1::class, $fixture);
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loader = new CachePoolFixturesLoader();
    }
}
