<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 4/24/14
 * Time: 12:33 PM
 */

namespace AC\NormBundle\core;

use AC\NormBundle\core\datastore\AbstractDatastore;


class NormBaseCollection extends \ArrayObject {

    /**
     * @var AbstractDatastore
     */
    private $_db;

    /**
     * @var AbstractDatastore
     */
    private $_cache;

    protected static $realm;
    protected static $tableName;
    protected static $singularClassName;
    protected static $primaryKeyFieldNames;
    protected static $primaryKeyPropertyNames;
    protected static $autoIncrementFieldName;

    /**
     * This function allows using any of the array_* functions with collections
     *
     * To use it, do something like:
     * $coll->array_keys();
     *
     * Note that you need to omit the first parameter to these functions as the array is automatically passed in
     *
     * @param $func
     * @param $argv
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($func, $argv)
    {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_')
        {
            throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }


    public function loadBySql($sql, $params) {
        $this->_db->readCollectionBySql($sql, $params, $this);
    }

    public function loadByIds($ids) {
        $this->_db->readCollection(static::$realm, static::$tableName, $ids, $this);
    }

    public function loadByWhere($where, $params) {
        $this->_db->readCollectionByWhere(static::$tableName, $where, $params, $this);
    }

    public function save() {
        $newObjectFieldData = array();

        foreach($this as $obj) {
            /** @var $obj NormBaseObject */
            if($obj->isNewObject() && !$obj->hasAutoIncrement()) {
                $newObjectFieldData[] = $obj->getFieldData();
            }
            else {
                $obj->save();
            }
        }

        if(count($newObjectFieldData) > 0) {
            $this->_db->createCollection(static::$realm, static::$tableName, $newObjectFieldData, static::$autoIncrementFieldName, true);
        }
    }

    public function delete() {
        $this->_db->deleteCollection(static::$realm, static::$tableName, $this->getPrimaryKeyData());
    }

    public function getPrimaryKeyData() {
        $data = array();

        foreach($this as $obj) {
            /** @var $obj NormBaseObject */
            $data[] = $obj->getPrimaryKeyData();
        }

        return $data;
    }

    public function getJson() {
        $arr = array();

        foreach($this as $id => $obj) {
            $arr[$id] = $obj->getJson();
        }

        return json_encode($arr);
    }

    public function loadByJson($json) {
        $arr = json_decode($json, true);
        foreach($arr as $id => $objJson) {
            $obj = new static::$singularClassName();
            $obj->loadByJson($objJson);
            $this[$id] = $obj;
        }
    }
} 