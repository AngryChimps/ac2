<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:13 PM
 */

namespace AC\NormBundle\core\datastore;

use AC\NormBundle\core\Utils;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\Builder;
use Elastica\Search;
use AC\NormBundle\core\NormBaseCollection;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

class ElasticsearchDatastore extends AbstractDatastore {
    /** @var  \Elastica\Client */
    private $client;

    /** @var  \Elastica\Index */
    private $index;

    /** @var LoggerInterface */
    protected $loggerService;
    protected $realmInfo;

    protected $defaultAnalyzer;

    public function __construct($configParams, RealmInfoService $realmInfo, LoggerInterface $loggerService) {

        $this->client = new Client(array('servers' => $configParams['servers']));
        $this->index = $this->client->getIndex($configParams['index_name']);
        $this->realmInfo = $realmInfo;
        $this->loggerService = $loggerService;
        $this->defaultAnalyzer = $configParams['default_analyzer'];
    }

    public function createObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with times if necessary
        if(property_exists($obj, 'createdAt')) {
            $obj->createdAt = new \DateTime();
        }

        $data = $this->getAsArray($obj);
        $type = $this->index->getType($tableInfo['name']);
        $doc = new Document($this->getIdentifier($obj), $data);
        $type->addDocument($doc);
        $type->getIndex()->refresh();
    }

    public function updateObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with times if necessary
        if(property_exists($obj, 'updatedAt')) {
            $obj->updatedAt = new \DateTime();
        }

        $data = $this->getAsArray($obj);
        $type = $this->index->getType($tableInfo['name']);
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
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        $type = $this->index->getType($tableInfo['name']);
        $type->deleteById($this->getIdentifier($obj));
        $type->getIndex()->refresh();
    }

    public function deleteCollection($coll, &$debug) {
        foreach($coll as $key => $obj) {
            $this->deleteObject($obj, $debug);
        }
    }

    protected function getAsArray($obj) {
        return (array) $obj;
    }

    public function getKeyName($primaryKeys) {
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('Y-m-d H:i:s');
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
     * @param $tableName
     * @param $query
     * @param int $limit
     * @param int $offset
     * @return \Elastica\ResultSet
     */
    public function search($tableName, $query, $limit = 10, $offset = 0)
    {
//        $query = new Builder($query);
//        $query = new Query($query->toArray());
        $search = new Search($this->client);
        $resultSet = $search->addIndex($this->index->getName())
                            ->addType($tableName)
                            ->search($query);
        return $resultSet;
    }

    public function publish($indexName, $identifier, array $data)
    {
        $this->loggerService->info('Publishing ad :: '
            . json_encode(
                array (
                    'indexName' => $indexName,
                    'identifier' => $identifier,
                    'data' => $data,
                )));
        $type = $this->index->getType($indexName);
        $doc = new Document($identifier, $data);
        $type->addDocument($doc);
        $type->getIndex()->refresh();
    }

    public function deleteType($indexName) {
        $type = $this->index->getType($indexName);
        $type->delete();
    }

    public function defineMapping($indexName, $properties) {
        //Create the index
        $this->index->create(
            [
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
            ],
            true
        );

        //Create a type
        $elasticaType = $this->index->getType($indexName);

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        $mapping->setParam('index_analyzer', $this->defaultAnalyzer);
        $mapping->setParam('search_analyzer', $this->defaultAnalyzer);

        $mapping->setProperties($properties);
        $mapping->send();
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

    public function readBySecondaryIndex($realm, $tableName, $indexName, $value) {
        $bucket = $this->getBucket($realm, $tableName);

        $response = $bucket->index($indexName, $value);

        if(empty($response)) {
            return null;
        }

        if($response->hasObject()) {
            $content = $response->getFirstObject();
            $json = $content->getContent();
            return json_decode($json);
        }
        else {
            return null;
        }
    }


    public function populateObjectByPks($obj, $pks, &$debug)
    {
        // TODO: Implement populateObjectByPks() method.
    }

    public function populateCollectionByPks($obj, $pks, &$debug)
    {
        // TODO: Implement populateCollectionByPks() method.
    }
}