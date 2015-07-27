<?php


namespace AC\NormBundle\services;

use AC\NormBundle\core\exceptions\UnsupportedObjectType;
use AC\NormBundle\core\Utils;
use Psr\Log\LoggerInterface;
use AC\NormBundle\Collector\NormDataCollector;

class NormCrudService
{
    protected static $debugData = array();
    protected $debug;

    /** @var InfoService  */
    protected $infoService;

    /** @var  LoggerInterface */
    protected $loggerService;

    /** @var DatastoreService */
    protected $datastoreService;

    /** @var NormDataCollector */
    protected $dataCollector;

    public function __construct($debug, InfoService $infoService, LoggerInterface $loggerService,
                                DatastoreService $datastoreService, NormDataCollector $dataCollector) {
        $this->debug = $debug;
        $this->infoService = $infoService;
        $this->loggerService = $loggerService;
        $this->datastoreService = $datastoreService;
        $this->dataCollector = $dataCollector;
    }

    public function create($obj, $data = null) {
        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startCreateQuery($obj);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);
        $ds = $this->datastoreService->getDatastore($this->infoService->getDatastoreName($class));

        if($this->isCollection($obj)) {
            $ds->createCollection($obj, $data, $debug);
        }
        else {
            $ds->createObject($obj, $data, $debug);
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
        $ds = $this->datastoreService->getDatastore($this->infoService->getDatastoreName($class));

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
        $ds = $this->datastoreService->getDatastore($this->infoService->getDatastoreName($class));

        if($this->isCollection($obj)) {
            $ds->deleteCollection($obj, $debug);
        }
        else {
            $ds->deleteObject($obj, $debug);
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

        $ds = $this->datastoreService->getDatastore($this->infoService->getDatastoreName($className));
        if($ds->populateObjectByPks($obj, $pks, $debug) === false) {
            if ($this->debug) {
                $this->dataCollector->endQueryFailed($debug, (array) $obj);
            }
            return null;
        }

        //Use setId() to manually overwrite the default id (__contruct() sets a default value)
        if(method_exists($obj, 'setId')) {
            $obj->setId($pks[0]);
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, (array) $obj);
        }

        return $obj;
    }

    protected function getCollectionByPks($className, $pks) {
        $coll = new $className();
        $tableInfo = $this->infoService->getTableInfo($className);

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
        $tableInfo = $this->infoService->getTableInfo($class);

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

    public function getApiPublicArray($obj) {
        $entityName = $this->infoService->getEntityName(get_class($obj));
        $fields = $this->infoService->getApiPublicFields($entityName);

        $arr = [];
        foreach($fields as $field) {
            $func = 'get' . ucfirst(Utils::field2property($field));
            $arr[$field] = $obj->$func();
        }

        return $arr;
    }

    public function getApiPrivateArray($obj) {
        $entityName = $this->infoService->getEntityName(get_class($obj));
        $fields = array_merge($this->infoService->getApiPrivateFields($entityName),
            $this->infoService->getApiPublicFields($entityName));

        $arr = [];
        foreach($fields as $field) {
            $func = 'get' . ucfirst(Utils::field2property($field));
            $arr[$field] = $obj->$func();
        }

        return $arr;
    }

    public function getAsArray($obj) {
        $entityName = $this->infoService->getEntityName(get_class($obj));
        $fields = $this->infoService->getFieldNames($entityName);

        $arr = [];
        foreach($fields as $field) {
            $func = 'get' . ucfirst(Utils::field2property($field));
            $value = $obj->$func();
            if(is_object($value)) {
                $arr[$field] = (array) $value;
            }
            else {
                $arr[$field] = $value;
            }
        }

        return $arr;
    }
}