<?php declare(strict_types=1);

namespace Kununu\DataFixtures\Purger;

use Elasticsearch\Client;
use stdClass;

final class ElasticSearchPurger implements PurgerInterface
{
    private $elasticSearch;

    private $indexName;

    public function __construct(Client $elasticSearch, string $indexName)
    {
        $this->elasticSearch = $elasticSearch;
        $this->indexName = $indexName;
    }

    public function purge(): void
    {
        $this->elasticSearch->deleteByQuery(
            [
                'index' => $this->indexName,
                'body'  => [
                    'query' => [
                        'match_all' => new stdClass(),
                    ],
                ],
                //'conflicts' => 'proceed',
                'wait_for_completion' => true,
            ]
        );
    }
}
