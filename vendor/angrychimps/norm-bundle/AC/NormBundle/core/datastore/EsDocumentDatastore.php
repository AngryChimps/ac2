<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:13 PM
 */

namespace AC\NormBundle\core\datastore;

use AC\NormBundle\core\exceptions\MethodNotImplemented;
use AC\NormBundle\core\Utils;
use AC\NormBundle\Services\InfoService;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\Builder;
use Elastica\Search;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

class EsDocumentDatastore extends AbstractElasticsearchDatastore {
    /** @var  \Elastica\Client */
    private $client;

    /** @var  \Elastica\Index */
    private $index;

    public function __construct(array $configParams, InfoService $infoService, LoggerInterface $loggerService)
    {
        parent::__construct($infoService, $loggerService);

        $servers = [];
        foreach($configParams['servers'] as $server) {
            $servers[] = ['host' => $server['host'], 'port' => $server['port']];
        }

        $this->client = new \Elastica\Client(array(
            'servers' => $servers
        ));

        $this->index = $this->client->getIndex($configParams['index_name']);
    }


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


        $data = $this->getAsArray($obj);
        $type = $this->index->getType($this->infoService->getEntityName(get_class($obj)));
        $doc = new Document($this->getIdentifier($obj), $data);
        $type->addDocument($doc);
        $type->getIndex()->refresh();
    }

    public function updateObject($obj, &$debug)
    {
        //Deal with times if necessary
        if(property_exists($obj, 'updatedAt')) {
            $obj->updatedAt = new \DateTime();
        }

        if($debug !== null) {
            $key = $this->getKeyAsString($this->getIdentifier($obj));
            $arr = [];
            $arr['key'] = $key;
            $debug['updateObject'][] = $arr;
            $this->loggerService->info('Updating object: ' . json_encode($debug));
        }

        $data = $this->getAsArray($obj);
        $type = $this->index->getType($this->infoService->getEntityName(get_class($obj)));
        $doc = new Document($this->getIdentifier($obj), $data);
        $type->addDocument($doc);
        $type->getIndex()->refresh();
    }

    public function createCollection($coll, &$debug) {
        foreach($coll as $key => $obj) {
            $this->createObject($obj, $debug);
        }
    }

    public function updateCollection($coll, &$debug) {
        foreach($coll as $key => $obj) {
            $this->updateObject($obj, $debug);
        }
    }

    public function deleteObject($obj, &$debug) {
        $tableInfo = $this->infoService->getTableInfo(get_class($obj));

        $type = $this->index->getType($tableInfo['name']);
        $type->deleteById($this->getIdentifier($obj));
        $type->getIndex()->refresh();
    }

    public function deleteCollection($coll, &$debug) {
        foreach($coll as $key => $obj) {
            $this->deleteObject($obj, $debug);
        }
    }

    public function getKeyAsString($primaryKeys) {
        if(!is_array($primaryKeys)) {
            $primaryKeys = [$primaryKeys];
        }
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('c');
            }
        }
        return implode('|', $primaryKeys);
    }

//    public function create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
//    {
//        if(!empty($autoIncrementFieldName)) {
//            throw new \Exception('Auto-increment fields are not supported in the ElasticsearchDatastore');
//        }
//        $fdata = $this->normalizeFieldData($fieldData);
//        $type = $this->index->getType($tableName);
//        $doc = new Document($this->getKeyName($primaryKeys), $fdata);
//        $type->addDocument($doc);
//        $type->getIndex()->refresh();
//    }

//    public function read($realm, $tableName, $primaryKeys)
//    {
//        $type = $this->index->getType($tableName);
//        $resultSet = $type->getDocument($this->getKeyName($primaryKeys));
//        print_r($resultSet);
//        exit;


//        $bucket = $this->getBucket($realm, $tableName);
//        $key = $this->getKeyName($primaryKeys);
//
//        $response = $bucket->get($key);
//
//        if(!is_object($response)) {
//            return null;
//        }
//
//        if(!$response->hasObject()) {
//            return null;
//        }
//
//        if($response->hasObject()) {
//            $content = $response->getFirstObject();
//            $json = $content->getContent();
//            return json_decode($json, true);
//        }
//        else {
//            return null;
//        }
//    }

    /**
     * @param $entityName
     * @param $query Query
     * @param int $limit
     * @param int $offset
     * @param array $debug
     * @return \Elastica\ResultSet
     */
    public function search($entityName, $query, $limit, $offset, &$debug)
    {
        $query->setFrom($offset);
        $query->setSize($limit);

        if($debug) {
            $debug['queryArray'] = $query->getQuery();
        }

        $search = new Search($this->client);
        $resultSet = $search->addIndex($this->index->getName())
                            ->addType($entityName)
                            ->search($query);
        return $resultSet;
    }

    public function populateObjectByQuery($obj, $query, $limit, $offset, &$debug)
    {
        $query->setFrom($offset);
        $query->setSize($limit);

        if($debug) {
            $debug['queryArray'] = $query->getQuery();
        }

        $entityName = $this->infoService->getEntityName(get_class($obj));
        $search = new Search($this->client);
        $resultSet = $search->addIndex($this->index->getName())
            ->addType($entityName)
            ->search($query);

        if($resultSet->getTotalHits() === 0) {
            return false;
        }
        if($resultSet->getTotalHits() > 1) {
            throw new \Exception('Query returned more than one result');
        }

        foreach($resultSet->getResults() as $result) {
            $sourceArray = $result->getSource();
            $obj->setRiakMap($this->getMapValues($obj, $sourceArray));
        }

        return true;
    }

    public function populateCollectionByQuery(\ArrayObject $coll, $query, $limit, $offset, &$debug)
    {
        $query->setFrom($offset);
        $query->setSize($limit);

        if($debug) {
            $debug['queryArray'] = $query->getQuery();
        }

        $entityName = $this->infoService->getEntityName(get_class($coll));
        $singularClassName = $this->infoService->getClassName($entityName);
        $search = new Search($this->client);
        $resultSet = $search->addIndex($this->index->getName())
            ->addType($entityName)
            ->search($query);

        if($resultSet->getTotalHits() === 0) {
            return false;
        }

        foreach($resultSet->getResults() as $result) {
            $obj = new $singularClassName();
            $sourceArray = $result->getSource();
            $obj->setRiakMap($this->getMapValues($obj, $sourceArray));
            $coll[$this->getIdentifier($obj)] = $obj;
        }

        return true;
    }

//    public function publish($indexName, $identifier, array $data)
//    {
//        $this->loggerService->info('Publishing ad :: '
//            . json_encode(
//                array (
//                    'indexName' => $indexName,
//                    'identifier' => $identifier,
//                    'data' => $data,
//                )));
//        $type = $this->index->getType($indexName);
//        $doc = new Document($identifier, $data);
//        $type->addDocument($doc);
//        $type->getIndex()->refresh();
//    }

    public function deleteType($indexName) {
        $type = $this->index->getType($indexName);
        $type->delete();
    }

    public function defineMapping($typeName) {
        //Create properties array
        $props = $this->getMappingProperties($typeName);

        //Create a type
        $elasticaType = $this->index->getType($typeName);

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setParam('index_analyzer', 'default');
        $mapping->setParam('search_analyzer', 'default');

        $mapping->setProperties($props);
        $mapping->send();
    }

    public function getMappingProperties($entity) {
        $props = [];
        $fields = $this->infoService->getFieldNames($entity);
        $types = $this->infoService->getFieldTypes($entity);

        for($i = 0; $i < count($fields); $i++) {
            $props[$fields[$i]] = $this->getMappingPropertiesForField($types[$i]);
        }

        return $props;
    }

    protected function getMappingPropertiesForSubclass($subclassType) {
        $subclassName = $this->infoService->getSubclassName($subclassType);
        $props = [];
        $fields = $this->infoService->getSubclassFieldNames($subclassName);
        $types = $this->infoService->getSubclassFieldTypes($subclassName);

        for($i = 0; $i < count($fields); $i++) {
            $props[$fields[$i]] = $this->getMappingPropertiesForField($types[$i]);
        }

        return $props;
    }

    protected function getMappingPropertiesForField($type) {
        switch (rtrim($type, '[]')) {
            case 'Uuid':
            case 'Email':
                return ['type' => 'string', 'index' => 'not_analyzed'];

            case 'Location':
                return ['type' => 'geo_point'];

            case 'string':
            case 'text':
            case 'Time':
                return ['type' => 'string'];

            case 'int':
            case 'Counter':
            case 'enum':
                return ['type' => 'integer'];

            case 'bool':
                return ['type' => 'boolean'];

            case 'float':
            case 'double':
            case 'Currency':
            case 'decimal':
                return ['type' => 'float'];

            case 'Date':
            case 'DateTime':
                return ['type' => 'date'];

            case 'set':
                return ['type' => 'float'];

            default:
                return ['type' => 'object', 'properties' => $this->getMappingPropertiesForSubclass($type)];
        }
    }

    public function createIndex($shards, $replicas) {
        $this->index->create(
            [
                'number_of_shards' => $shards,
                'number_of_replicas' => $replicas,
            ]
        );
    }

    public function deleteIndex() {
        $this->index->delete();
    }

//    public function update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
//    {
//        $fData = $this->normalizeFieldData($fieldDataWithoutPrimaryKeys);
//        $pkData = $this->normalizeFieldData($primaryKeys);
//
//        $bucket = $this->getBucket($realm, $tableName);
//        $key = $this->getKeyName($primaryKeys);
//        $data = json_encode(array_merge($pkData, $fData));
//
//        // Read back the object from Riak
//        $response = $bucket->get($key);
//
//        // Make sure we got an object back
//        if ($response->hasObject()) {
//            // Get the first returned object
//            $readObject = $response->getFirstObject();
//        }
//        else {
//            throw new \Exception('Original object not found; unable to update.');
//        }
//        $readObject->setContent($data);
//        $bucket->put($readObject);
//    }
//
//    public function delete($realm, $tableName, $primaryKeys)
//    {
//        $bucket = $this->getBucket($realm, $tableName);
//        $key = $this->getKeyName($primaryKeys);
//
//        // Read back the object from Riak
//        $response = $bucket->get($key);
//
//        // Make sure we got an object back
//        if ($response->hasObject()) {
//            // Get the first returned object
//            $readObject = $response->getFirstObject();
//        }
//        else {
//            throw new \Exception('Original object not found; unable to update.');
//        }
//
//        $bucket->delete($readObject);
//    }
//
//    public function createCollection($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
//    {
//        for($i=0; $i<count($primaryKeys); $i++) {
//            $this->create($realm, $tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
//        }
//    }
//
//    public function readCollection($realm, $tableName, $primaryKeys)
//    {
//        $arr = array();
//        foreach($primaryKeys as $pk) {
//            $arr[] = $this->read($realm, $tableName, $pk);
//        }
//        return $arr;
//    }
//
//    public function updateCollection($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
//    {
//        for($i=0; $i<count($primaryKeys); $i++) {
//            $this->update($realm, $tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys[$i]);
//        }
//    }
//
//    public function deleteCollection($realm, $tableName, $primaryKeys)
//    {
//        for($i=0; $i<count($primaryKeys); $i++) {
//            $this->delete($realm, $tableName, $primaryKeys[$i]);
//        }
//    }
//
//    public function readBySql($sql, $params)
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function readByWhere($tableName, $where, $params)
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function readCollectionBySql($sql, $params)
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function readCollectionByWhere($tableName, $where, $params)
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function query($sql, $params = array())
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function queryOneValue($sql, $params = array())
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function queryOneColumn($sql, $params = array())
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }
//
//    public function getDbName()
//    {
//        throw new MethodNotImplemented(__METHOD__, get_called_class());
//    }

//    public function readBySecondaryIndex($realm, $tableName, $indexName, $value) {
//        $bucket = $this->getBucket($realm, $tableName);
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
//            return json_decode($json);
//        }
//        else {
//            return null;
//        }
//    }


    public function populateObjectByPks($obj, $pks, &$debug)
    {
        // TODO: Implement populateObjectByPks() method.
    }

    public function populateCollectionByPks($obj, $pks, &$debug)
    {
        // TODO: Implement populateCollectionByPks() method.
    }

    public function getQueryResultsCount($className, $query, &$debug)
    {
        // TODO: Implement getQueryResultsCount() method.
    }


}