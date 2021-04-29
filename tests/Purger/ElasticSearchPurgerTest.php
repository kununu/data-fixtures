<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ElasticSearchPurgerTest extends TestCase
{
    public function testPurge(): void
    {
        $elasticSearch = $this->createMock(Client::class);

        $indices = $this->createMock(IndicesNamespace::class);
        $indices
            ->expects($this->exactly(2))
            ->method('refresh')
            ->with(['index' => 'my_index']);

        $elasticSearch
            ->expects($this->any())
            ->method('indices')
            ->willReturn($indices);

        $elasticSearch
            ->expects($this->once())
            ->method('deleteByQuery')
            ->with(
                [
                    'index' => 'my_index',
                    'body'  => [
                        'query' => [
                            'match_all' => new stdClass(),
                        ],
                    ],
                    'conflicts' => 'proceed',
                ]
            )
            ->willReturn(true);

        $purger = new ElasticSearchPurger($elasticSearch, 'my_index');
        $purger->purge();
    }
}
