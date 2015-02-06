<?php


namespace AC\NormBundle\Services\traits;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\datastore\AbstractPdoDatastore;

trait PdoTrait {
    protected function getObjectByWhere($className, $where, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $obj = new $className();

        if($debug !== null) {
            $debug = $this->dataCollector->startReadQuery($obj);
        }
        else {
            $debug = null;
        }

        /** @var AbstractPdoDatastore $ds */
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo, $this->loggerService);
        $ds->populateObjectByWhere($obj, $where, $params, $debug);

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $obj);
        }

        return $obj;
   }

    protected function getObjectBySql($className, $sql, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $obj = new $className();

        if($debug !== null) {
            $debug = $this->dataCollector->startReadQuery($obj);
        }
        else {
            $debug = null;
        }

        /** @var AbstractPdoDatastore $ds */
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className));
        $ds->populateObjectBySql($obj, $sql, $params, $debug);

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $obj);
        }

        return $obj;
    }

    protected function getCollectionBySql($className, $sql, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();

        if($debug !== null) {
            $debug = $this->dataCollector->startReadQuery($coll);
        }
        else {
            $debug = null;
        }

        /** @var AbstractPdoDatastore $ds */
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className));
        $ds->populateCollectionBySql($coll, $sql, $params, $debug);

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $coll);
        }

        return $coll;
    }

    protected function getCollectionByWhere($className, $where, $params = array(), &$debug = null) {
        if(!class_exists($className)) {
            throw new \Exception('Invalid class name');
        }
        $coll = new $className();

        if($debug !== null) {
            $debug = $this->dataCollector->startReadQuery($className, array_merge(array('where' => $where), $params));;
        }
        else {
            $debug = null;
        }

        /** @var AbstractPdoDatastore $ds */
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className));
        $ds->populateCollectionByWhere($coll, $where, $params, $debug);

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $coll);
        }

        return $coll;
    }
}