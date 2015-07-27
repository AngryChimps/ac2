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

class RedisHashDatastore extends AbstractRedisDatastore {

    protected function _getKeyName($tableName, $primaryKeys) {
        return '__norm:hash:' . $tableName . ':' . implode('_', $primaryKeys);
    }

    public function create($tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        if(!empty($autoIncrementFieldName)) {
            $autoIncrementValue = $this->connection->incr('__norm:sequence:' . $tableName);
            $fieldData[$autoIncrementFieldName] = $autoIncrementValue;
        }
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $this->connection->del($key);
        $this->connection->hMSet($key, $fieldData);

        if(!empty($autoIncrementFieldName)) {
            return $autoIncrementValue;
        }
    }

    public function read($tableName, $primaryKeys)
    {
        $fieldData = $this->connection->hGetAll($this->_getKeyName($tableName, $primaryKeys));
        return $fieldData;
    }

    public function update($tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $this->connection->hMset($this->_getKeyName($tableName, $primaryKeys),
                                 array_merge($primaryKeys, $fieldDataWithoutPrimaryKeys));
    }

    public function delete($tableName, $primaryKeys)
    {
        $key = $this->_getKeyName($tableName, $primaryKeys);
        $this->connection->del($key);
    }

    public function createCollection($tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->create($tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
        }
    }

    public function readCollection($tableName, $primaryKeys)
    {
        $arr = array();
        foreach($primaryKeys as $pk) {
            $arr[] = $this->read($tableName, $pk);
        }
        return $arr;
    }

    public function updateCollection($tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->update($tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys[$i]);
        }
    }

    public function deleteCollection($tableName, $primaryKeys)
    {
        for($i=0; $i<count($primaryKeys); $i++) {
            $this->delete($tableName, $primaryKeys[$i]);
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