<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:13 PM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\Utils;

class RiakBlobDatastore extends AbstractRiakDatastore {

    public function create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        if(!empty($autoIncrementFieldName)) {
            throw new \Exception('Auto-increment fields are not supported in the RiakBlobDatastore');
        }
        $bucket = $this->getBucket($realm, $tableName);
        $key = $this->getKeyName($primaryKeys);
        $data = json_encode($fieldData);

        $obj = new \Riak\Object($key);
        $obj->setContent($data);
        $bucket->put($obj);
    }

    public function read($realm, $tableName, $primaryKeys)
    {
        $bucket = $this->getBucket($realm, $tableName);
        $key = $this->getKeyName($primaryKeys);

        $response = $bucket->get($key);

        if(!is_object($response)) {
            return null;
        }

        if(!$response->hasObject()) {
            return null;
        }

        if($response->hasObject()) {
            $content = $response->getFirstObject();
            $json = $content->getContent();
            return json_decode($json, true);
        }
        else {
            return null;
        }
    }

    public function update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        $bucket = $this->getBucket($realm, $tableName);
        $key = $this->getKeyName($primaryKeys);
        $data = json_encode(array_merge($primaryKeys, $fieldDataWithoutPrimaryKeys));

        // Read back the object from Riak
        $response = $bucket->get($key);

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $readObject = $response->getFirstObject();
        }
        else {
            throw new \Exception('Original object not found; unable to update.');
        }
        $readObject->setContent($data);
        $bucket->put($readObject);
    }

    public function delete($realm, $tableName, $primaryKeys)
    {
        $bucket = $this->getBucket($realm, $tableName);
        $key = $this->getKeyName($primaryKeys);

        // Read back the object from Riak
        $response = $bucket->get($key);

        // Make sure we got an object back
        if ($response->hasObject()) {
            // Get the first returned object
            $readObject = $response->getFirstObject();
        }
        else {
            throw new \Exception('Original object not found; unable to update.');
        }

        $bucket->delete($readObject);
    }

    public function createCollection($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->create($realm, $tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
        }
    }

    public function readCollection($realm, $tableName, $primaryKeys)
    {
        $arr = array();
        foreach($primaryKeys as $pk) {
            $arr[] = $this->read($realm, $tableName, $pk);
        }
        return $arr;
    }

    public function updateCollection($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->update($realm, $tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys[$i]);
        }
    }

    public function deleteCollection($realm, $tableName, $primaryKeys)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->delete($realm, $tableName, $primaryKeys[$i]);
        }
    }

    public function readBySql($sql, $params)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readByWhere($tableName, $where, $params)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readCollectionBySql($sql, $params)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readCollectionByWhere($tableName, $where, $params)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function query($sql, $params = array())
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function queryOneValue($sql, $params = array())
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function queryOneColumn($sql, $params = array())
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function getDbName()
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

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


}