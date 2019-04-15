<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Kununu\DataFixtures\Adapter\CachePoolFixtureInterface;
use Kununu\DataFixtures\Loader\CachePoolFixturesLoader;
use Kununu\DataFixtures\Tests\TestFixtures\CachePoolFixture1;
use Kununu\DataFixtures\Tests\TestFixtures\CachePoolFixture2;
use Kununu\DataFixtures\Tests\TestFixtures\NotAFixture;
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

    public function testLoadFromDirectoryThrowsExceptionIfNotDirectory()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromDirectory(__DIR__. '/../NotFoundDirectory/');
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

    public function testLoadFromFileThrowsExceptionForInvalidFile()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->loadFromFile(__DIR__. '/../NotFoundDirectory/CachePoolFixture1.php');
    }

    public function testLoadFromClassName()
    {
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock1')->getMock()
        );
        $this->loader->addFixture(
            $this->getMockBuilder(CachePoolFixtureInterface::class)->setMockClassName('Mock2')->getMock()
        );

        $this->assertCount(2, $this->loader->getFixtures());

        $this->loader->loadFromClassName(CachePoolFixture1::class);
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromClassName(NotAFixture::class);
        $this->assertCount(3, $this->loader->getFixtures());
        $this->loader->loadFromClassName(CachePoolFixture2::class);
        $this->assertCount(4, $this->loader->getFixtures());
    }

    public function testGetFixture()
    {
        $this->loader->loadFromFile(__DIR__. '/../TestFixtures/CachePoolFixture1.php');

        $fixture = $this->loader->getFixture(CachePoolFixture1::class);

        $this->assertInstanceOf(CachePoolFixture1::class, $fixture);
    }

    public function testGetFixtureThrowsExceptionWhenFixtureDoesNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->loader->getFixture(CachePoolFixture1::class);
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loader = new CachePoolFixturesLoader();
    }
}
