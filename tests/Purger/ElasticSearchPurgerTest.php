<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Purger;

use Elasticsearch\Client;
use Kununu\DataFixtures\Purger\ElasticSearchPurger;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ElasticSearchPurgerTest extends TestCase
{
    public function testPurge(): void
    {
        $elasticSearch = $this->createMock(Client::class);

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
                ]
            )
            ->willReturn(true);

        $purger = new ElasticSearchPurger($elasticSearch, 'my_index');
        $purger->purge();
    }
}
