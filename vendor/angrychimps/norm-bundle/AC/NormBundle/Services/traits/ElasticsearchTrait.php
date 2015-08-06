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
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
        return $ds->search($this->realmInfo->getTableName($className), $query, $limit, $offset);
    }

    public function publishObject($obj, array $data) {
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore(get_class($obj)), $this->realmInfo, $this->loggerService);
        return $ds->publish($this->realmInfo->getTableName(get_class($obj)), $this->getIdentifier($obj), $data);
    }

    public function deleteIndex($className, $indexName) {
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
        return $ds->deleteIndex($indexName);
    }

    public function defineMapping($className, $properties){
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
        return $ds->defineMapping($this->realmInfo->getTableName($className), $properties);
    }
}