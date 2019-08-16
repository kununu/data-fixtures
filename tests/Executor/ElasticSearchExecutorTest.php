<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Loader;

use Elasticsearch\Client;
use Kununu\DataFixtures\Adapter\ElasticSearchFixtureInterface;
use Kununu\DataFixtures\Executor\ElasticSearchExecutor;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ElasticSearchExecutorTest extends TestCase
{
    /** @var Client|MockObject */
    private $client;

    /** @var PurgerInterface|MockObject */
    private $purger;

    public function testThatDoesNotPurgesWhenAppendIsEnabled(): void
    {
        $this->purger
            ->expects($this->never())
            ->method('purge');

        $executor = new ElasticSearchExecutor($this->client, $this->purger);

        $executor->execute([], true);
    }

    public function testThatPurgesWhenAppendIsDisabled(): void
    {
        $this->purger
            ->expects($this->once())
            ->method('purge');

        $executor = new ElasticSearchExecutor($this->client, $this->purger);

        $executor->execute([]);
    }

    public function testThatFixturesAreLoaded(): void
    {
        $fixture1 = $this->createMock(ElasticSearchFixtureInterface::class);
        $fixture1->expects($this->once())->method('load')->with($this->client);

        $fixture2 = $this->createMock(ElasticSearchFixtureInterface::class);
        $fixture2->expects($this->once())->method('load')->with($this->client);

        $executor = new ElasticSearchExecutor($this->client, $this->purger);

        $executor->execute([$fixture1, $fixture2]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->purger = $this->createMock(PurgerInterface::class);
    }
}
