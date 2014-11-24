<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:17 PM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\exceptions\MethodNotImplemented;
use AC\NormBundle\core\NormBaseObject;

class RedisBlobDatastore extends AbstractRedisDatastore {

    public function __construct($configParams) {
        $this->connection = new \Redis();
        $this->connection->connect($configParams['hostname'], $configParams['port']);
    }

    public function create($tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        if(!empty($autoIncrementFieldName)) {
            $autoIncrementValue = $this->connection->incr('__norm:sequence:' . $tableName);
            $fieldData[$autoIncrementFieldName] = $autoIncrementValue;
        }
        $this->connection->set($this->_getKeyName($tableName, $primaryKeys), json_encode($fieldData));
        if(!empty($autoIncrementFieldName)) {
            return $autoIncrementFieldName;
        }
    }

    protected function _getKeyName($tableName, $primaryKeys) {
        return '__norm:blob:' . $tableName . ':' . implode('_', $primaryKeys);
    }

    public function read($tableName, $primaryKeys, NormBaseObject $normObject)
    {
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $json = $this->connection->get($key);
        $normObject->loadByJson($json);
    }

    public function update($tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $this->connection->set($key, array_merge($primaryKeys, $fieldDataWithoutPrimaryKeys));
    }

    public function delete($tableName, $primaryKeys)
    {
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $this->connection->del($key);
    }

    public function createCollection($tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        $ids = array();
        $this->connection->multi(\Redis::PIPELINE);
        for($i=0; $i < count($fieldData); $i++) {
                $ids[] = $this->create($tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
        }
        $this->connection->exec();

        if(!empty($autoIncrementFieldName)) {
            return $ids;
        }
    }

    public function readCollection($tableName, $primaryKeys, $normCollection)
    {
        for($i=0; $i < count($primaryKeys); $i++) {
            $this->read($tableName, $primaryKeys[$i], $normCollection[$i]);
        }
    }

    public function updateCollection($tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        for($i=0; $i < count($primaryKeys); $i++) {
            $this->update($tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys[$i]);
        }
    }

    public function deleteCollection($tableName, $primaryKeys)
    {
        foreach($primaryKeys as $pk) {
            $this->delete($tableName, $pk);
        }
    }

    public function readBySql($sql, $params, $normObject)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readByWhere($tableName, $where, $params, $normObject)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readCollectionBySql($sql, $params, $normCollection)
    {
        throw new MethodNotImplemented(__METHOD__, get_called_class());
    }

    public function readCollectionByWhere($tableName, $where, $params, $normCollection)
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

}