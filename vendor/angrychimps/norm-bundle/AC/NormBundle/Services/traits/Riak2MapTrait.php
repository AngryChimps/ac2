<?php


namespace AC\NormBundle\services\traits;
use AC\NormBundle\core\datastore\Riak2MapDatastore;

trait Riak2MapTrait {
    public function getCollectionByQuery($className, $indexName, $query, $limit, $offset, &$debug = null)
    {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();

        if($debug !== null) {
            $debug = array();
            $debug['method'] = 'getCollectionByQuery';
            $debug['className'] = $className;
            $debug['indexName'] = $indexName;
        }

        /** @var Riak2MapDatastore $ds */
        $ds = $this->datastoreService->getDatastore($this->infoService->getDatastore($className));
        $success = $ds->populateCollectionByQuery($indexName, $coll, $query, $limit, $offset, $debug);

        if(!$success) {
            return null;
        }
        return $coll;
    }
}