<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Executor;

use Kununu\DataFixtures\Adapter\OpenSearchFixtureInterface;
use Kununu\DataFixtures\Executor\ExecutorInterface;
use Kununu\DataFixtures\Executor\OpenSearchExecutor;
use OpenSearch\Client;
use OpenSearch\Namespaces\IndicesNamespace;
use PHPUnit\Framework\MockObject\MockObject;

final class OpenSearchExecutorTest extends AbstractExecutorTestCase
{
    private const string INDEX_NAME = 'my_index';

    private MockObject&Client $client;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects(self::never())
            ->method('purge');

        $this->executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects(self::once())
            ->method('purge');

        $this->executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(OpenSearchFixtureInterface::class);
        $fixture1
            ->expects(self::once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $fixture2 = $this->createMock(OpenSearchFixtureInterface::class);
        $fixture2
            ->expects(self::once())
            ->method('load')
            ->with($this->client, self::INDEX_NAME);

        $indices = $this->createMock(IndicesNamespace::class);
        $indices
            ->expects(self::once())
            ->method('refresh')
            ->with(['index' => self::INDEX_NAME]);

        $this->client
            ->expects(self::any())
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
        return new OpenSearchExecutor($this->client, self::INDEX_NAME, $this->purger);
    }
}
