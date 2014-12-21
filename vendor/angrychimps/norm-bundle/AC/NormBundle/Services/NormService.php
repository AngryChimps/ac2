<?php


namespace AC\NormBundle\Services;

use AC\NormBundle\core\datastore\DatastoreManager;
use AC\NormBundle\core\exceptions\UnsupportedObjectType;
use AC\NormBundle\core\exceptions\UnsupportedObjectTypeException;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
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


    /**
     * @param $debug bool Whether to enable the data collection for the profiler
     * @param $realmInfo RealmInfoService Injected through dependency injection
     */
    public function __construct($debug, RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        $this->debug = $debug;
        $this->realmInfo = $realmInfo;
        $this->loggerService = $loggerService;
    }

    public function create($obj) {
        //Setup Debugging
        if ($this->debug) {
            $debug = $this->getObjDebug($obj);
            $debug['method'] = 'create';
            $timer = microtime(true);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);
        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($class), $this->realmInfo,
                                             $this->loggerService);

        if($this->isCollection($obj)) {
            $ds->createCollection($obj, $debug);
        }
        else {
            $ds->createObject($obj, $debug);
        }

        //Store debugging data
        if ($this->debug) {
            $debug['time'] = microtime(true) - $timer;
            $this->loggerService->info('Creating object: ' . json_encode($debug));
            $this->addDebugData($debug);
            $this->loggerService->info('Created object: ' . json_encode($debug));
        }
    }

    public function update($obj)
    {
        if ($this->debug) {
            $debug = $this->getObjDebug($obj);
            $debug['method'] = 'update';
            $timer = microtime(true);
        }
        else {
            $debug = null;
        }

        //Get datastore
        $class = get_class($obj);
        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($class), $this->realmInfo,
            $this->loggerService);

        if($this->isCollection($obj)) {
            $ds->updateCollection($obj, $debug);
        }
        else {
            $ds->updateObject($obj, $debug);
        }

        //Store debugging data
        if ($this->debug) {
            $debug['time'] = microtime(true) - $timer;
            $this->addDebugData($debug);
            $this->loggerService->info('Updated object: ' . json_encode($debug));
        }
    }

    public function delete($obj) {
        if ($this->debug) {
            $debug = $this->getObjDebug($obj);
            $debug['method'] = 'deleteObject';
            $timer = microtime(true);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);
        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($class), $this->realmInfo,
            $this->loggerService);

        if($this->isCollection($obj)) {
            $ds->deleteCollection($obj, $this->realmInfo->getRealm($class),
                $this->realmInfo->getTableName($class), $debug);
        }
        else {
            $ds->deleteObject($obj, $this->realmInfo->getRealm($class),
                $this->realmInfo->getTableName($class), $debug);
        }

        if($this->debug) {
            $debug['time'] = microtime(true) - $timer;
            $this->addDebugData($debug);
            $this->loggerService->info('Deleted object: ' . json_encode($debug));
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
            $debug = array();
            $debug['method'] = 'getObjectByPks';
            $debug['className'] = $className;
            $debug['pks'] = $pks;
            $timer = microtime(true);
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

        $ds = DatastoreManager::getDatastore($this->realmInfo->getDatastore($className), $this->realmInfo,
            $this->loggerService);

        if($ds->populateObjectByPks($obj, $pkData, $debug) === false) {
            if ($this->debug) {
                $debug['time'] = microtime(true) - $timer;
                $debug['obj'] = $this->getObjDebug($obj)['obj'];
                $this->addDebugData($debug);
                $this->loggerService->info('Got object by pks: ' . json_encode($debug));
            }
            return null;
        }

        if ($this->debug) {
            $debug['time'] = microtime(true) - $timer;
            $debug['obj'] = $this->getObjDebug($obj)['obj'];
            $this->addDebugData($debug);
            $this->loggerService->info('Got object by pks: ' . json_encode($debug));
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

    //Debug data is used by the NormDataCollector class to populate debugging profile info
    public function collectDebugData() {
        $count = count(self::$debugData);

        if($count === 0) {
            return ['querycount' => 0, 'time' => 0, 'queries' => []];
        }

        $time = 0;
        foreach(self::$debugData['queries'] as $query) {
            $time += $query['time'];
        }
        self::$debugData['time'] = $time;
        self::$debugData['querycount'] = $count;
        return self::$debugData;
    }

    protected function addDebugData(array $value) {
        self::$debugData['queries'][] = $value;
    }

    protected function getObjDebug($obj) {
        $debug = array();
        $debug['className'] = get_class($obj);
        if($this->isCollection($obj)) {
            $debug['collectionCount'] = count($obj);
        }

        $debug['obj'] = $obj;

        return $debug;
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
                $pkArray = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d H:i:s');
            }
            elseif($tableInfo['fieldTypes'][$i] === 'Date') {
                $pkArray = $obj->$tableInfo['primaryKeyPropertyNames'][$i]->format('Y-m-d');
            }
            else {
                $pkArray = $obj->$tableInfo['primaryKeyPropertyNames'][$i];
            }
        }

        return implode('|', $pkArray);
    }

}