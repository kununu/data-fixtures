<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Kununu\DataFixtures\Purger\ElasticsearchPurger;
use Kununu\DataFixtures\Purger\PurgerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

final class ElasticsearchPurgerTest extends AbstractPurgerTestCase
{
    private MockObject&Client $client;

    public function testPurge(): void
    {
        $indices = $this->createMock(IndicesNamespace::class);
        $indices
            ->expects($this->exactly(2))
            ->method('refresh')
            ->with(['index' => 'my_index']);

        $this->client
            ->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        $this->client
            ->expects($this->once())
            ->method('deleteByQuery')
            ->with(
                [
                    'index'     => 'my_index',
                    'body'      => [
                        'query' => [
                            'match_all' => new stdClass(),
                        ],
                    ],
                    'conflicts' => 'proceed',
                ]
            )
            ->willReturn(true);

        $this->purger->purge();
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        parent::setUp();
    }

    protected function getPurger(): PurgerInterface
    {
        return new ElasticsearchPurger($this->client, 'my_index');
    }
}
