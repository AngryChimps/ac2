<?php


namespace AC\NormBundle\Services\traits;

use AC\NormBundle\core\datastore\DatastoreManager;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\Builder;
use Elastica\Search;
use AC\NormBundle\core\datastore\ElasticsearchDatastore;

trait ElasticsearchTrait {
    /** @var  \Elastica\Client */
    private $client;

    /** @var  \Elastica\Index */
    private $index;

    /**
     * @param $className
     * @param $query
     * @param int $limit
     * @param int $offset
     * @return \Elastica\ResultSet
     */
    public function search($className, $query, $limit = 10, $offset = 0)
    {
        /** @var ElasticsearchDatastore $ds */
        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo);
        return $ds->search($className, $query, $limit, $offset);
    }
} 