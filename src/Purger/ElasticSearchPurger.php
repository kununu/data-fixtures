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
        // We need this flush/clearCache twice otherwise we may hit version conflicts when messing up with the same document.
        $this->elasticSearch->indices()->flush(['index' => $this->indexName, 'force' => true]);
        $this->elasticSearch->indices()->clearCache(['index' => $this->indexName]);

        $this->elasticSearch->deleteByQuery(
            [
                'index' => $this->indexName,
                'body'  => [
                    'query' => [
                        'match_all' => new stdClass(),
                    ],
                ],
                'refresh'             => true,
                'wait_for_completion' => true,
            ]
        );

        $this->elasticSearch->indices()->flush(['index' => $this->indexName, 'force' => true]);
        $this->elasticSearch->indices()->clearCache(['index' => $this->indexName]);
    }
}
