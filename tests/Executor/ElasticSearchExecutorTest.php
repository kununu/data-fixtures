<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchExecutorTest extends TestCase
{
    private const INDEX_NAME = 'my_index';

    /** @var Client|MockObject */
    private $client;

    /** @var PurgerInterface|MockObject */
    private $purger;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $executor = new ElasticSearchExecutor($this->client, self::INDEX_NAME, $this->purger);

        $executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $executor = new ElasticSearchExecutor($this->client, self::INDEX_NAME, $this->purger);

        $executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(ElasticSearchFixtureInterface::class);
        $fixture1
            ->expects($this->once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $fixture2 = $this->createMock(ElasticSearchFixtureInterface::class);
        $fixture2
            ->expects($this->once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $indices = $this->createMock(IndicesNamespace::class);
        $indices
            ->expects($this->once())
            ->method('flush')
            ->with(['index' => self::INDEX_NAME, 'force' => true]);

        $indices
            ->expects($this->once())
            ->method('clearCache')
            ->with(['index' => self::INDEX_NAME]);

        $this->client
            ->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        $executor = new ElasticSearchExecutor($this->client, self::INDEX_NAME, $this->purger);

        $executor->execute([$fixture1, $fixture2]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->purger = $this->createMock(PurgerInterface::class);
    }
}
