<?php

namespace AC\NormBundle\core\datastore;

use AC\NormBundle\core\Utils;
use Riak\Client\Command\Kv\DeleteValue;
use Riak\Client\Command\DataType\FetchMap;
use AC\NormBundle\services\InfoService;
use Psr\Log\LoggerInterface;
use Riak\Client\Command\Search\Search;
use Riak\Client\Command\DataType\StoreMap;
use Riak\Client\Core\Query\RiakNamespace;
use Riak\Client\Core\Query\RiakLocation;

class Riak2MapDatastore extends AbstractRiak2Datastore {
    public function __construct($configParams, InfoService $infoService,
                                LoggerInterface $loggerService) {
        parent::__construct($configParams, $infoService, $loggerService);
    }

    public static function createObject($obj, $data, &$debug)
    {
        if($data !== null) {
            foreach($data as $field => $value) {
                $func = 'set' . ucfirst(Utils::field2property($field));
                $obj->$func($value);
            }
        }

        //Deal with created_at if necessary
        if(method_exists($obj, 'setCreatedAt')) {
            $obj->setCreatedAt(new \DateTime());
        }
        $data = self::getAsArray($obj);
        $json = json_encode($data);

        if($debug !== null) {
            $key = self::getKeyAsString(self::getIdentifier($obj));
            $arr = [];
            $arr['key'] = $key;
            $arr['data'] = $json;
            $debug['createObject'][] = $arr;
            self::$loggerService->info('Creating object: ' . json_encode($debug));
        }

        self::storeObject($obj, $debug);
    }

    protected static function storeObject($obj, &$debug) {
        $riakLocation  = self::getRiakLocation($obj);
        $riakStore = $obj->getRiakStoreMapBuilder()
            ->withLocation($riakLocation)
            ->build();

        self::$riakClient->execute($riakStore);
    }

    public static function createCollection($coll, $data, &$debug)
    {
        if($data === null) {
            for($i = 0; $i < count($coll); $i++) {
                self::createObject($coll[$i], $data, $debug);
            }
        }
        else {
            for($i = 0; $i < count($coll); $i++) {
                self::createObject($coll[$i], $data[$i], $debug);
            }
        }
    }

    public static function updateObject($obj, &$debug)
    {
        //Deal with times if necessary
        if(method_exists($obj, 'setUpdatedAt')) {
            $obj->setUpdatedAt(new \DateTime());
        }
        $data = self::getAsArray($obj);
        $key = self::getKeyAsString(self::getIdentifier($obj));
        $data = json_encode($data);

        if($debug !== null) {
            $arr = [];
            $arr['key'] = $key;
            $debug['updateObject'][] = $arr;
            self::$loggerService->info('Updating object: ' . json_encode($debug));
        }

        self::storeObject($obj, $debug);
    }

    public static function updateCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            self::updateObject($coll[$i], $debug);
        }
    }

    public static function deleteObject($obj, &$debug)
    {
        $key = self::getKeyAsString(self::getIdentifier($obj));

        if($debug !== null) {
            $arr = [];
            $arr['key'] = $key;
            $debug['deleteObject'][] = $arr;
            self::$loggerService->info('Deleting object: ' . json_encode($debug));
        }

        $delete  = DeleteValue::builder($obj->getRiakLocation())
            ->withPw(1)
            ->withW(2)
            ->build();

        self::$riakClient->execute($delete);

    }

    public static function deleteCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            self::deleteObject($coll[$i], $debug);
        }
    }

    public static function populateObjectByPks($obj, $pks, &$debug)
    {
        if($debug !== null) {
            $arr = [];
            $debug['populateObjectByPks'][] = $arr;
            self::$loggerService->info('Populating object by primary keys: ' . json_encode($debug));
        }

        $fetch = FetchMap::builder()
            ->withLocation(self::getRiakLocation($obj, $pks))
            ->build();

        $result = self::$riakClient->execute($fetch);
        $map = $result->getDatatype();

        $obj->setRiakMap($map);

        return true;
    }

    public static function populateCollectionByQuery($indexName, \ArrayObject $coll, $query, $limit, $offset, &$debug) {
//        if($debug !== null) {
//            $arr = [];
//            $debug['populateObjectByPks'][] = $arr;
//            self::$loggerService->info('Populating object by primary keys: ' . json_encode($debug));
//        }

        $tableInfo = self::$infoService->getTableInfo(get_class($coll));

        $search = Search::builder()
            ->withQuery($query)
            ->withIndex($indexName)
            ->withNumRows($limit)
            ->withStart($offset)
            ->build();

        $results = $search->getAllResults();

        foreach($results as $result) {
            $object = new $tableInfo['objectName']();

            foreach($result as $fieldName => $data) {
                if(strpos($fieldName, '_yz_') === 0) {
                    $function = 'get' . ucfirst(Utils::field2property($fieldName));
                    $object->$function($data);
                }
            }

            $coll[self::getIdentifier($object)] = $object;
        }

        return true;
    }

    protected static function getRiakLocation($obj, $pks = null)
    {
        $tableInfo = self::$infoService->getTableInfo(get_class($obj));

        $namespace = new RiakNamespace(self::$riakNamespacePrefix . 'class_maps', $tableInfo['name']);

        if ($pks !== null) {
            if (!is_array($pks)) {
                $pks = [$pks];
            }
            $location = new RiakLocation($namespace, implode('|', $pks));
        }
        else {
            $location = new RiakLocation($namespace, self::getIdentifier($obj));
        }

        return $location;
    }

    public static function populateCollectionByPks($coll, $pks, &$debug) {
        //For a collection $pks would be an array of ids or an array of an array of ids
        $tableInfo = self::$infoService->getTableInfo(get_class($coll));

        foreach($pks as $pk) {
            $object = new $tableInfo['objectName']();

            if(self::populateObjectByPks($object, $pk, $debug) === false) {
                throw new \Exception('Unable to find one or more objects to populate the collection.');
            }

            $coll[self::getIdentifier($object)] = $object;
        }
    }

//    public static function populateCollectionByQuery(\ArrayObject $coll, $query, $realm, $tableName, $bucketType, $bucketName,
//                                                     &$debug, $limit = null, $offset = 0) {
//        $builder = Search::builder();
//        $builder->withIndex('__norm_classmaps_' . self::$infoService->getTableName);
//        if($limit !== null) {
//            $builder->withNumRows($limit);
//        }
//        if($offset !== 0) {
//            $builder->withStart($offset);
//        }
//        $builder->withQuery($query);
//
//        $search = $builder->build();
//
//        $searchResult = self::$riakClient->execute($search);
//        $results = $searchResult->getAllResults();
//
//        foreach($results as $result) {
//            $className = self::$infoService->getClassName($realm, $tableName);
//            $obj = new $className();
//
//            $bucketType = $result["_yz_rt"];
//            $bucketName = $result["yz_rb"];
//            $key        = $result["_yz_rk"];
//
//            // create reference object locations
//            $namespace = new RiakNamespace($bucketType , $bucketName);
//            $location  = new RiakLocation($namespace, $key);
//
//            // fetch object
//            $fetch  = FetchValue::builder($location)
//                ->withNotFoundOk(true)
//                ->withR(1)
//                ->build();
//
//            /** @var $result \Riak\Client\Command\Kv\Response\FetchValueResponse */
//            /** @var $object \Riak\Client\Core\Query\RiakObject */
//            $result = self::$riakClient->execute($fetch);
//            $object = $result->getValue();
//
//        }
//    }

//    public function populateCollectionBySecondaryIndex(NormBaseCollection $coll, $indexName, $value, &$debug = null) {
//        $bucket = self::getBucket($coll::$realm, $coll::$tableName);
//
//        $response = $bucket->index($indexName, $value);
//
//        if(empty($response)) {
//            return null;
//        }
//
//        if($response->hasObject()) {
//            $content = $response->getFirstObject();
//            $json = $content->getContent();
//            $this->populateCollectionByOrderedArray($coll, json_decode($json, true));
//        }
//        else {
//            return null;
//        }
//    }
//
//    protected function populateObjectByOrderedArray(NormBaseObject $obj, array $arr) {
//        for($i = 0; $i < count($obj::$fieldNames); $i++) {
//            $fieldType = $obj::$fieldTypes[$i];
//            $propertyName = $obj::$propertyNames[$i];
//            $value = $arr[$i];
//
//            if(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseObject", class_parents($fieldType))) {
//                $object = new $fieldType();
//                $this->populateObjectByOrderedArray($object, json_decode($value));
//                $obj->$propertyName = $object;
//            }
//            elseif(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseCollection", class_parents($fieldType))) {
//                $object = new $fieldType();
//                $this->populateObjectByOrderedArray($object, json_decode($value));
//                $obj->$propertyName = $object;
//            }
//            else {
//                switch($fieldType) {
//                    case 'int':
//                        $obj->$propertyName = (int) $value;
//                        break;
//                    case 'bool':
//                        $obj->$propertyName = (bool) $value;
//                        break;
//                    case 'float':
//                        $obj->$propertyName = (float) $value;
//                        break;
//                    case 'Date':
//                    case 'DateTime':
//                        $obj->$propertyName = new \DateTime($value);
//                        break;
//                    case 'int[]':
//                    case 'float[]':
//                    case 'double[]':
//                    case 'string[]':
//                        $obj->$propertyName = $value;
//                        break;
//                    default:
//                        $obj->$propertyName = $value;
//                }
//            }
//
//        }
//    }
//
//    protected function populateCollectionByOrderedArray(NormBaseCollection $coll, array $arr) {
//        foreach($arr as $objArr) {
//            $objectClass = $coll::$singularClassName;
//            $obj = new $objectClass();
//            $this->populateObjectByOrderedArray($obj, $objArr);
//            $coll[self::getIdentifier($obj)] = $obj;
//        }
//    }

}