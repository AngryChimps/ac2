<?php


namespace AC\NormBundle\Services;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectType;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use Norm\es\ProviderAdListing;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use AC\NormBundle\Collector\NormDataCollector;

class NormService
{
    protected static $debugData = array();
    protected $debug;

    /** @var RealmInfoService  */
    protected $realmInfo;

    /** @var  LoggerInterface */
    protected $loggerService;

    /** @var DatastoreService */
    protected $datastoreService;

    /** @var NormDataCollector */
    protected $dataCollector;

    /**
     * @param $debug bool Whether to enable the data collection for the profiler
     * @param $realmInfo RealmInfoService Injected through dependency injection
     */
    public function __construct($debug, RealmInfoService $realmInfo, LoggerInterface $loggerService,
                                DatastoreService $datastoreService, NormDataCollector $dataCollector) {
        $this->debug = $debug;
        $this->realmInfo = $realmInfo;
        $this->loggerService = $loggerService;
        $this->datastoreService = $datastoreService;
        $this->dataCollector = $dataCollector;
    }

    public function create($obj) {
        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startCreateQuery($obj);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($class));

        if($this->isCollection($obj)) {
            $ds->createCollection($obj, $debug);
        }
        else {
            $ds->createObject($obj, $debug);
        }

        //Store debugging data
        if ($this->debug) {
            $this->dataCollector->endQuery($debug);
        }
    }

    public function update($obj)
    {
        if ($this->debug) {
            $debug = $this->dataCollector->startUpdateQuery($obj);
        }
        else {
            $debug = null;
        }

        //Get datastore
        $class = get_class($obj);
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($class));

        if($this->isCollection($obj)) {
            $ds->updateCollection($obj, $debug);
        }
        else {
            $ds->updateObject($obj, $debug);
        }

        //Store debugging data
        if ($this->debug) {
            $this->dataCollector->endQuery($debug);
        }
    }

    public function delete($obj) {
        if ($this->debug) {
            $debug = $this->dataCollector->startDeleteQuery($obj);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);
        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($class));

        if($this->isCollection($obj)) {
            $ds->deleteCollection($obj, $this->realmInfo->getRealm($class),
                $this->realmInfo->getTableName($class), $debug);
        }
        else {
            $ds->deleteObject($obj, $this->realmInfo->getRealm($class),
                $this->realmInfo->getTableName($class), $debug);
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug);
        }
    }

    public function invalidate($obj) {
        //Eventually when local caching comes back, this will be necessary
    }

//    public function getObjectAsArray(NormBaseObject $obj) {
//        $data = array();
//
//        for($i = 0; $i < count($obj::$fieldNames); $i++) {
//            $data[$obj::$fieldNames[$i]] = $obj->{$obj::$propertyNames[$i]};
//        }
//
//        return $data;
//    }
//
//    public function getCollectionAsArray(NormBaseCollection $coll) {
//        $data = array();
//
//        foreach($coll as $obj) {
//            $objData = array();
//            for($i = 0; $i < count($obj::$fieldNames); $i++) {
//                $objData[$obj::$fieldNames[$i]] = $obj->{$obj::$propertyNames[$i]};
//            }
//            $data[] = $objData;
//        }
//
//        return $data;
//    }
//
//    public function getObjectAsJson(NormBaseObject $obj) {
//        return json_encode($this->getObjectAsArray($obj));
//    }
//
//    public function getCollectionAsJson(NormBaseCollection $coll) {
//        return json_encode($this->getCollectionAsArray($coll));
//    }

    protected function getObjectByPks($className, $pks)
    {
        $obj = new $className();

        if ($this->debug) {
            $debug = $this->dataCollector->startReadQuery($className, $pks);
        }
        else {
            $debug = null;
        }

        if(!is_array($pks)) {
            $pks = [$pks];
        }

        $primaryKeyFieldNames = $this->realmInfo->getPkFieldNames($obj);
        $pkData = [];
        for($i = 0; $i < count($pks); $i++) {
            $pkData[array_values($primaryKeyFieldNames)[$i]] = array_values($pks)[$i];
        }

        $ds = $this->datastoreService->getDatastore($this->realmInfo->getDatastore($className));

        if($ds->populateObjectByPks($obj, $pkData, $debug) === false) {
            if ($this->debug) {
                $this->dataCollector->endQueryFailed($debug, (array) $obj);
            }
            return null;
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $obj);
        }

        return $obj;
    }

    protected function getCollectionByPks($className, $pks) {
        $coll = new $className();
        $tableInfo = $this->realmInfo->getTableInfo($className);

        foreach($pks as $pk) {
            $object = $this->getObjectByPks($tableInfo['objectName'], $pk);
            if($object === false) {
                throw new \Exception('Unable to find one or more objects in collection.');
            }

            $coll[$this->getIdentifier($object)] = $object;
        }

        return $coll;
    }

    public function isCollection($obj) {
        $class = get_class($obj);
        return strpos($class, 'Collection') == strlen($class) - 10;
    }

    protected function getIdentifier($obj) {
        $class = get_class($obj);
        $tableInfo = $this->realmInfo->getTableInfo($class);

        $pkArray = [];
        for($i = 0; $i < count($tableInfo['primaryKeyPropertyNames']); $i++) {
            if($tableInfo['fieldTypes'][$i] === 'DateTime') {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d H:i:s');
            }
            elseif($tableInfo['fieldTypes'][$i] === 'Date') {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d');
            }
            else {
                $pkArray[] = $obj->$tableInfo['primaryKeyPropertyNames'][$i];
            }
        }

        return implode('|', $pkArray);
    }

}