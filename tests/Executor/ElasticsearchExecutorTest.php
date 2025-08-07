<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Kununu\DataFixtures\Adapter\ElasticsearchFixtureInterface;
use Kununu\DataFixtures\Executor\ElasticsearchExecutor;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class ElasticsearchExecutorTest extends AbstractExecutorTestCase
{
    private const string INDEX_NAME = 'my_index';

    private MockObject&Client $client;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(ElasticsearchFixtureInterface::class);
        $fixture1
            ->expects($this->once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $fixture2 = $this->createMock(ElasticsearchFixtureInterface::class);
        $fixture2
            ->expects($this->once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $indices = $this->createMock(IndicesNamespace::class);
        $indices
            ->expects($this->once())
            ->method('refresh')
            ->with(['index' => self::INDEX_NAME]);

        $this->client
            ->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        $this->executor->execute([$fixture1, $fixture2]);
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        parent::setUp();
    }

    protected function getExecutor(): ExecutorInterface
    {
        return new ElasticsearchExecutor($this->client, self::INDEX_NAME, $this->purger);
    }
}
