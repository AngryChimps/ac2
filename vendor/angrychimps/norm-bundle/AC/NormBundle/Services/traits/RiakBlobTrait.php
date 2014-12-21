<?php


namespace AC\NormBundle\Services\traits;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\datastore\RiakBlobDatastore;

trait RiakBlobTrait {
    public function getObjectBySecondaryIndex($className, $indexName, $value, &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $obj = new $className();

        if($obj instanceof NormBaseObject) {
            if($debug !== null) {
                $debug = array();
                $debug['method'] = 'getObjectBySecondaryIndex';
                $debug['className'] = $className;
                $debug['indexName'] = $indexName;
                $debug['indexValue'] = $value;
                $timer = microtime(true);
            }

            /** @var RiakBlobDatastore $ds */
            $ds = DatastoreManager::getDatastore($className::$primaryDatastoreName);
            $ds->populateObjectBySecondaryIndex($obj, $indexName, $value, $debug);

            if($debug !== null) {
                $debug['time'] = microtime(true) - $timer;
                parent::addDebugData($debug);
            }
        }
        else {
            throw new UnsupportedObjectTypeException('class not supported: ' . get_class($obj));
        }

        return $obj;
    }

    public function getCollectionBySecondaryIndex($className, $indexName, $value, &$debug = null)
    {
        if (!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();


        if ($coll instanceof NormBaseObject) {
            if ($debug !== null) {
                $debug = array();
                $debug['method'] = 'getCollectionBySecondaryIndex';
                $debug['className'] = $className;
                $debug['indexName'] = $indexName;
                $debug['indexValue'] = $value;
                $timer = microtime(true);
            }

            /** @var RiakBlobDatastore $ds */
            $ds = DatastoreManager::getDatastore($className::$primaryDatastoreName);
            $ds->populateCollectionBySecondaryIndex($coll, $indexName, $value, $debug);

            if ($debug !== null) {
                $debug['time'] = microtime(true) - $timer;
                parent::addDebugData($debug);
            }
        } else {
            throw new UnsupportedObjectTypeException('class not supported: ' . get_class($obj));
        }

        return $coll;
    }
}