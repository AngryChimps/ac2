<?php


namespace AC\NormBundle\Services;

use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\exceptions\UnsupportedObjectType;
use AC\NormBundle\core\Utils;
use AngryChimps\TaskBundle\Services\Tasks\NormCreateObjectTask;
use Psr\Log\LoggerInterface;
use AC\NormBundle\Collector\NormDataCollector;

abstract class NormCrudService
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

    public function create($obj, $dsName = null) {
        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startCreateQuery($obj);
        }
        else {
            $debug = null;
        }

        $class = get_class($obj);

        if($dsName === null) {
            $ds = $this->datastoreService->getDatastore($this->infoService->getPrimaryDatastoreName($class));
        }
        else {
            $ds = $this->datastoreService->getDatastore($dsName);
        }

        if($this->isCollection($obj)) {
            $ds->createCollection($obj, $debug);
        }
        else {
            $ds->createObject($obj, $debug);
        }

        //Create tasks for secondary datastores
        foreach($this->infoService->getSecondaryDatastoreNames($class) as $dsName) {
//            $task = new NormCreateObjectTask($obj, $this, $dsName);
            $ds = $this->datastoreService->getDatastore($dsName);
            if($this->isCollection($obj)) {
                $ds->createCollection($obj, $debug);
            }
            else {
                $ds->createObject($obj, $debug);
            }
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
        $ds = $this->datastoreService->getDatastore($this->infoService->getPrimaryDatastoreName($class));

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
        $ds = $this->datastoreService->getDatastore($this->infoService->getPrimaryDatastoreName($class));

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

    protected function getObjectByPks($className, $pks, $datastoreName = null)
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

        if($datastoreName === null) {
            $datastoreName = $this->infoService->getPrimaryDatastoreName($className);
        }

        $ds = $this->datastoreService->getDatastore($datastoreName);
        if($ds->populateObjectByPks($obj, $pks, $debug) === false) {
            if ($this->debug) {
                $this->dataCollector->endQueryFailed($debug, $this->getAsArray($obj));
            }
            return null;
        }

        //Use setId() to manually overwrite the default id (__contruct() sets a default value)
        if(method_exists($obj, 'setId')) {
            $obj->setId($pks[0]);
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, $this->getAsArray($obj));
        }

        return $obj;
    }

    protected function getObjectByQuery($className, $query, $limit = null, $offset = 0, $datastoreName = null) {
        $obj = new $className();

        if($datastoreName === null) {
            $datastoreName = $this->infoService->getPrimaryDatastoreName($className);
        }

        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startReadByQueryQuery($className, $query, $limit, $offset, $datastoreName);
        }
        else {
            $debug = null;
        }

        $ds = $this->datastoreService->getDatastore($datastoreName);
        if($ds->populateObjectByQuery($obj, $query, $limit, $offset, $debug) === false) {
            return null;
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, $this->getAsArray($obj));
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

    protected function getCollectionByQuery($className, $query, $limit = null, $offset = 0, $datastoreName = null) {
        $coll = new $className();

        if($datastoreName === null) {
            $datastoreName = $this->infoService->getPrimaryDatastoreName($className);
        }

        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startReadCollectionByQueryQuery($className, $query, $limit, $offset, $datastoreName);
        }
        else {
            $debug = null;
        }

        $ds = $this->datastoreService->getDatastore($datastoreName);
        if($ds->populateCollectionByQuery($coll, $query, $limit, $offset, $debug) === false) {
            return null;
        }

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, $this->getAsArray($coll));
        }

        return $coll;
    }

    protected function getCountByQuery($className, $query, $datastoreName = null) {
        if($datastoreName === null) {
            $datastoreName = $this->infoService->getPrimaryDatastoreName($className);
        }

        //Setup Debugging
        if ($this->debug) {
            $debug = $this->dataCollector->startCountByQueryQuery($className, $query, $datastoreName);
        }
        else {
            $debug = null;
        }

        $ds = $this->datastoreService->getDatastore($datastoreName);
        $count = $ds->getQueryResultsCount($className, $query, $debug);

        if ($this->debug) {
            $this->dataCollector->endQuery($debug, $count);
        }

        return $count;
    }

    public function isCollection($obj) {
        $class = get_class($obj);
        return strpos($class, 'Collection') === strlen($class) - 10;
    }

    protected function getIdentifier($obj) {
        $class = get_class($obj);
        $tableInfo = $this->infoService->getTableInfo($class);

        $pkArray = [];
        for($i = 0; $i < count($tableInfo['primaryKeyPropertyNames']); $i++) {
            $pkArray[] = $this->getApiFieldValue($obj->$tableInfo['primaryKeyPropertyNames'][$i], $tableInfo['fieldTypes'][$i]);
        }

        return implode('|', $pkArray);
    }

    public function getApiPublicArray($obj) {
        $arr = [];
        if(is_array($obj) || strpos(get_class($obj), 'Collection') > 0) {
            foreach($obj as $object) {
                $arr[] = $this->getApiPublicArray($object);
            }
        }
        else {
            $entityName = $this->infoService->getEntityName(get_class($obj));
            $publicFields = $this->infoService->getApiPublicFields($entityName);
            $publicFieldTypes = $this->infoService->getApiPublicFieldTypes($entityName);

            for($i = 0; $i < count($publicFields); $i++) {
                $func = 'get' . ucfirst(Utils::field2property($publicFields[$i]));
                $value = $obj->$func();
                $arr[$publicFields[$i]] = $this->getApiFieldValue($value, $publicFieldTypes[$i]);
            }
        }
        return $arr;
    }

    public function getApiPrivateArray($obj) {
        $arr = [];
        if(is_array($obj) || strpos(get_class($obj), 'Collection') > 0) {
            foreach($obj as $object) {
                $arr[] = $this->getApiPrivateArray($object);
            }
        }
        else {
            $entityName = $this->infoService->getEntityName(get_class($obj));
            $publicFields = $this->infoService->getApiPublicFields($entityName);
            $publicFieldTypes = $this->infoService->getApiPublicFieldTypes($entityName);

            for($i = 0; $i < count($publicFields); $i++) {
                $func = 'get' . ucfirst(Utils::field2property($publicFields[$i]));
                $value = $obj->$func();
                $arr[$publicFields[$i]] = $this->getApiFieldValue($value, $publicFieldTypes[$i]);
            }

            $privateFields = $this->infoService->getApiPrivateFields($entityName);
            $privateFieldTypes = $this->infoService->getApiPrivateFieldTypes($entityName);

            for($i = 0; $i < count($privateFields); $i++) {
                $func = 'get' . ucfirst(Utils::field2property($privateFields[$i]));
                $value = $obj->$func();
                $arr[$privateFields[$i]] = $this->getApiFieldValue($value, $privateFieldTypes[$i]);
            }
        }

        return $arr;
    }

    public function getAsArray($obj) {
        $arr = [];
        if(is_array($obj) || strpos(get_class($obj), 'Collection') > 0) {
            foreach($obj as $object) {
                $arr[] = $this->getAsArray($object);
            }
        }
        else {
            $entityName = $this->infoService->getEntityName(get_class($obj));
            $allFields = $this->infoService->getFieldNames($entityName);
            $allTypes = $this->infoService->getFieldTypes($entityName);

            for($i = 0; $i < count($allFields); $i++) {
                $func = 'get' . ucfirst(Utils::field2property($allFields[$i]));
                $value = $obj->$func();
                $arr[$allFields[$i]] = $this->getApiFieldValue($value, $allTypes[$i]);
            }
        }

        return $arr;
    }

    protected function getApiFieldValue($val, $type) {
        if($val === null) {
            return null;
        }
        $type = ltrim($type, '\\');
        switch($type) {
            case 'Date':
            case 'DateTime':
                return $val->format('c');

            case 'Date[]':
            case 'DateTime[]':
                $arr = [];
                foreach($val as $value) {
                    $arr[] = $value->format('c');
                }
                return $arr;

            case 'Location':
                list($lat, $lon) = explode(',', $val);
                return ['lat' => (float) $lat, 'lon' => (float) $lon];

            case 'Location[]':
                $arr = [];
                foreach($val as $value) {
                    list($lat, $lon) = explode(',', $value);
                    $arr[] = ['lat' => (float) $lat, 'lon' => (float) $lon];
                }
                return $arr;

            default:
                if(is_object($val)) {
                    return (array) $val;
                }
                else {
                    return $val;
                }
        }
    }
}