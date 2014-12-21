<?php


namespace AC\NormBundle\Services\traits;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\datastore\AbstractPdoDatastore;

trait PdoTrait {
    abstract protected function addDebugData(array $debug);

    protected function getObjectByWhere($className, $where, $params = array(), &$debug = null) {
    if(!class_exists($className)) {
        throw new \Exception('Invalid class name');
    }
    $obj = new $className();

        if($debug !== null) {
            $debug = array();
            $debug['method'] = 'getByWhere';
            $debug['className'] = $className;
            $timer = microtime(true);
        }

        /** @var AbstractPdoDatastore $ds */
        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
        $ds->populateObjectByWhere($obj, $where, $params, $debug);

        if($debug !== null) {
            $debug['time'] = microtime(true) - $timer;
            $this->addDebugData($debug);
        }

        return $obj;
   }

    protected function getObjectBySql($className, $sql, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $obj = new $className();

        if($obj instanceof NormBaseObject) {
            if($debug !== null) {
                $debug = array();
                $debug['method'] = 'getObjectBySql';
                $debug['className'] = $className;
                $timer = microtime(true);
            }

            /** @var AbstractPdoDatastore $ds */
            $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
            $ds->populateObjectBySql($obj, $sql, $params, $debug);

            if($debug !== null) {
                $debug['time'] = microtime(true) - $timer;
                $this->addDebugData($debug);
            }
        }
        else {
            throw new UnsupportedObjectTypeException('class not supported: ' . get_class($obj));
        }

        return $obj;
    }

    protected function getCollectionBySql($className, $sql, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();

        if($coll instanceof NormBaseCollection) {
            if($debug !== null) {
                $debug = array();
                $debug['method'] = 'getCollectionBySql';
                $debug['className'] = $className;
                $timer = microtime(true);
            }

            /** @var AbstractPdoDatastore $ds */
            $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
            $ds->populateCollectionBySql($coll, $sql, $params, $debug);

            if($debug !== null) {
                $debug['time'] = microtime(true) - $timer;
                $this->addDebugData($debug);
            }
        }
        else {
            throw new UnsupportedObjectTypeException('class not supported: ' . get_class($coll));
        }

        return $coll;
    }

    protected function getCollectionByWhere($className, $where, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();

        if($coll instanceof NormBaseCollection) {
            if($debug !== null) {
                $debug = array();
                $debug['method'] = 'getCollectionByWhere';
                $debug['className'] = $className;
                $timer = microtime(true);
            }

            /** @var AbstractPdoDatastore $ds */
            $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService     );
            $ds->populateCollectionByWhere($coll, $where, $params, $debug);

            if($debug !== null) {
                $debug['time'] = microtime(true) - $timer;
                $this->addDebugData($debug);
            }
        }
        else {
            throw new UnsupportedObjectTypeException('class not supported: ' . get_class($coll));
        }

        return $coll;
    }
}