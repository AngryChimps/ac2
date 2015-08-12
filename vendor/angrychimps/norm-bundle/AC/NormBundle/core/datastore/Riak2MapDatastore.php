<?php

namespace AC\NormBundle\core\datastore;

use AC\NormBundle\core\Utils;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Riak\Client\Command\Kv\DeleteValue;
use Riak\Client\Command\DataType\FetchMap;
use AC\NormBundle\services\InfoService;
use Psr\Log\LoggerInterface;
use Riak\Client\Command\Search\Search;
use Riak\Client\Command\DataType\StoreMap;
use Riak\Client\Core\Query\RiakNamespace;
use Riak\Client\Core\Query\RiakLocation;
use Riak\Client\Command\Bucket\StoreBucketProperties;
use Riak\Client\Command\Search\StoreIndex;
use Riak\Client\Core\Query\Search\YokozunaIndex;

class Riak2MapDatastore extends AbstractRiak2Datastore {

    public function createObject($obj, &$debug)
    {
        //Deal with created_at if necessary
        if(method_exists($obj, 'setCreatedAt')) {
            $obj->setCreatedAt(new \DateTime());
        }

        if($debug !== null) {
            $key = $this->getKeyAsString($this->getIdentifier($obj));
            $arr = [];
            $arr['key'] = $key;
            $debug['createObject'][] = $arr;
            $this->loggerService->info('Creating object: ' . json_encode($debug));
        }

        $this->storeObject($obj, $debug);
    }

    protected function storeObject($obj, &$debug) {
        $riakLocation  = $this->getRiakLocation($obj);
        $riakStore = $obj->getRiakStoreMapBuilder()
            ->withLocation($riakLocation)
            ->build();

        $this->riakClient->execute($riakStore);
    }

    public function createCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->createObject($coll[$i], $debug);
        }
    }

    public function updateObject($obj, &$debug)
    {
        //Deal with times if necessary
        if(method_exists($obj, 'setUpdatedAt')) {
            $obj->setUpdatedAt(new \DateTime());
        }

        if($debug !== null) {
            $key = $this->getKeyAsString($this->getIdentifier($obj));

            $arr = [];
            $arr['key'] = $key;
            $debug['updateObject'][] = $arr;
            $this->loggerService->info('Updating object: ' . json_encode($debug));
        }

        $this->storeObject($obj, $debug);
    }

    public function updateCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->updateObject($coll[$i], $debug);
        }
    }

    public function deleteObject($obj, &$debug)
    {
        $key = $this->getKeyAsString($this->getIdentifier($obj));

        if($debug !== null) {
            $arr = [];
            $arr['key'] = $key;
            $debug['deleteObject'][] = $arr;
            $this->loggerService->info('Deleting object: ' . json_encode($debug));
        }

        $delete  = DeleteValue::builder($obj->getRiakLocation())
            ->withPw(1)
            ->withW(2)
            ->build();

        $this->riakClient->execute($delete);

    }

    public function deleteCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->deleteObject($coll[$i], $debug);
        }
    }

    public function populateObjectByPks($obj, $pks, &$debug)
    {
        if($debug !== null) {
            $arr = [];
            $debug['populateObjectByPks'][] = $arr;
            $this->loggerService->info('Populating object by primary keys: ' . json_encode($debug));
        }

        $fetch = FetchMap::builder()
            ->withLocation($this->getRiakLocation($obj, $pks))
            ->build();

        $result = $this->riakClient->execute($fetch);

        $map = $result->getDatatype();

        if($map === null) {
            return false;
        }

        $obj->setRiakMap($map);

        return true;
    }

    public function search($entityName, $query, $limit, $offset, &$debug)
    {
        if($debug) {
            $debug['query'] = $query;
        }

        $indexName = $this->getPrefixedIndexName($entityName);

        if($limit !== null) {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withNumRows($limit)
                ->withStart($offset)
                ->build();
        }
        else {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withStart($offset)
                ->build();
        }
        return $this->riakClient->execute($search);
    }

    public function populateObjectByQuery($obj, $query, $limit, $offset, &$debug) {
        if($debug) {
            $debug['query'] = $query;
        }

        $entityName = $this->infoService->getEntityName(get_class($obj));
        $indexName = $this->getPrefixedIndexName($entityName);

        if($limit !== null) {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withNumRows($limit)
                ->withStart($offset)
                ->build();
        }
        else {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withStart($offset)
                ->build();
        }
        $searchResult  = $this->riakClient->execute($search);
        $numResults    = $searchResult->getNumResults();

        if($numResults === 0) {
            return false;
        }
        elseif($numResults === 1) {
            $singleResults = $searchResult->getSingleResults();
            $result = $singleResults[0];

            //remove the _register, _flag, etc. suffixes from the field names
            $arr = [];
            foreach($result as $fieldName => $value) {
                $arr[Utils::field2property(substr($fieldName, 0, strlen($fieldName) - (strlen($fieldName) - strrpos($fieldName, '_'))))] = $value[0];
            }

            $obj->setMapValues($arr);

            return true;
        }
        else {
            throw new \Exception('Multiple results returned when one was anticipated');
        }
    }

    public function populateCollectionByQuery(\ArrayObject $coll, $query, $limit, $offset, &$debug) {
        if($debug) {
            $debug['query'] = $query;
        }

        $tableInfo = $this->infoService->getTableInfo(get_class($coll));
        $indexName = $this->getPrefixedIndexName($this->infoService->getEntityName(get_class($coll)));

        if($limit !== null) {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withNumRows($limit)
                ->withStart($offset)
                ->build();
        }
        else {
            $search = Search::builder()
                ->withQuery($query)
                ->withIndex($indexName)
                ->withStart($offset)
                ->build();
        }
        $searchResult  = $this->riakClient->execute($search);

        $results = $searchResult->getAllResults();

        foreach($results as $result) {
            $object = new $tableInfo['objectName']();

            $object->setMapValues($this->getMapValuesFromRiakSearch($object, $result));
            $coll[] = $object;
        }

        return true;
    }

    protected function getMapValuesFromRiakSearch($obj, array $arr) {
        $arr2 = [];
        foreach($arr as $fieldName => $value) {
            $arr2[substr($fieldName, 0, strlen($fieldName) - (strlen($fieldName) - strrpos($fieldName, '_')))] = $value[0];
        }
        return parent::getMapValues($obj, $arr2);
    }

    public function getQueryResultsCount($className, $query, &$debug) {
        if($debug) {
            $debug['query'] = $query;
        }

        $indexName = $this->getPrefixedIndexName($this->infoService->getEntityName($className));

        $search = Search::builder()
            ->withQuery($query)
            ->withIndex($indexName)
            ->build();
        $searchResult  = $this->riakClient->execute($search);

        return $searchResult->getNumResults();
    }

    protected function getRiakLocation($obj, $pks = null)
    {
        $tableInfo = $this->infoService->getTableInfo(get_class($obj));

        $namespace = $this->getRiakNamespace($tableInfo['name']);

        if ($pks !== null) {
            if (!is_array($pks)) {
                $pks = [$pks];
            }
            $location = new RiakLocation($namespace, implode('|', $pks));
        }
        else {
            $location = new RiakLocation($namespace, $this->getIdentifier($obj));
        }

        return $location;
    }

    protected function getRiakNamespace($entityName) {
        return new RiakNamespace($this->getBucketType(), $entityName);
    }

    public function populateCollectionByPks($coll, $pks, &$debug) {
        //For a collection $pks would be an array of ids or an array of an array of ids
        $tableInfo = $this->infoService->getTableInfo(get_class($coll));

        foreach($pks as $pk) {
            $object = new $tableInfo['objectName']();

            if($this->populateObjectByPks($object, $pk, $debug) === false) {
                throw new \Exception('Unable to find one or more objects to populate the collection.');
            }

            $coll[$this->getIdentifier($object)] = $object;
        }
    }

    protected function getBucketType() {
        return $this->riakNamespacePrefix . 'class_maps';
    }

//    public static function populateCollectionByQuery(\ArrayObject $coll, $query, $realm, $tableName, $bucketType, $bucketName,
//                                                     &$debug, $limit = null, $offset = 0) {
//        $builder = Search::builder();
//        $builder->withIndex('__norm_classmaps_' . $this->infoService->getTableName);
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
//        $searchResult = $this->riakClient->execute($search);
//        $results = $searchResult->getAllResults();
//
//        foreach($results as $result) {
//            $className = $this->infoService->getClassName($realm, $tableName);
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
//            $result = $this->riakClient->execute($fetch);
//            $object = $result->getValue();
//
//        }
//    }

//    public function populateCollectionBySecondaryIndex(NormBaseCollection $coll, $indexName, $value, &$debug = null) {
//        $bucket = $this->getBucket($coll::$realm, $coll::$tableName);
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
//            $coll[$this->getIdentifier($obj)] = $obj;
//        }
//    }

    public function createSolrSchema($entityName, $schema) {
        $serverInfo = $this->configParams['servers'][0];

        $url = 'http://' . $serverInfo['host'] . ':' . $serverInfo['http_port'] . '/search/schema/' . $this->getPrefixedSchemaName($entityName);

        $request = $this->guzzleService->createRequest('PUT', $url, [
            'headers' => [
                'content-type' => 'application/xml'
            ],
            'body' => $schema,
        ]);

        $this->guzzleService->send($request);
    }

//    public function createSolrIndex($entityName) {
//        $serverInfo = $this->configParams['servers'][0];
//
//        $url = 'http://' . $serverInfo['host'] . ':' . $serverInfo['http_port'] . '/search/index/' . $this->getPrefixedIndexName($entityName);
//
//        $request = $this->guzzleService->createRequest('PUT', $url, [
//            'headers' => [
//                'content-type' => 'application/json'
//            ],
//            'body' => '{"schema":"' . $this->getPrefixedSchemaName($entityName) . '"}',
//        ]);
//
//        $this->guzzleService->send($request);
//    }

    public function createSolrIndex($entityName) {
        $index     = new YokozunaIndex($this->getPrefixedIndexName($entityName), $this->getPrefixedSchemaName($entityName));
        $command   = StoreIndex::builder()
            ->withIndex($index)
            ->build();

        $this->riakClient->execute($command);
    }

    public function associateBucketToSolrIndex($entityName)
    {
        $attempts = 20;
        $finished = false;

        while($attempts > 0) {
            try {
                $namespace = $this->getRiakNamespace($entityName);
                $command = StoreBucketProperties::builder()
                    ->withSearchIndex($this->getPrefixedIndexName($entityName))
                    ->withNamespace($namespace)
                    ->build();

                $this->riakClient->execute($command);
                $finished = true;
                break;
            }
            catch (\Riak\Client\Core\Transport\RiakTransportException  $ex) {
                sleep(5);
                $attempts--;
            }
        }

        if(!$finished) {
            throw new \Exception('associateBucketToSolrIndex ran out of attempts for entity: ' . $entityName);
        }
    }

    protected function getPrefixedSchemaName($indexName)
    {
        return $this->riakNamespacePrefix . 'schemas_'. $indexName;
    }

    protected function getPrefixedIndexName($indexName)
    {
        return $this->riakNamespacePrefix . 'indexes_' . $indexName;
    }

}