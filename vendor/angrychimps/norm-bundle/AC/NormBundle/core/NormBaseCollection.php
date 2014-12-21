<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 4/24/14
 * Time: 12:33 PM
 */

namespace AC\NormBundle\core;

use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\datastore\DatastoreManager;
use BadMethodCallException;


class NormBaseCollection extends \ArrayObject {

    /**
     * @var AbstractDatastore
     */
    protected static $_db;

    /**
     * @var AbstractDatastore
     */
    private $_cache;

    public static $realm;
    public static $tableName;
    public static $singularClassName;
    public static $primaryKeyFieldNames;
    public static $primaryKeyPropertyNames;
    public static $autoIncrementFieldName;
    public static $primaryDatastoreName;

    public function __construct()
    {
        self::$_db = DatastoreManager::getDatastore(static::$primaryDatastoreName);
    }

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
        self::$_db->readCollectionBySql($sql, $params, $this);
    }

    public function loadByIds($ids) {
        self::$_db->readCollection(static::$realm, static::$tableName, $ids, $this);
    }

    public function loadByWhere($where, $params) {
        self::$_db->readCollectionByWhere(static::$tableName, $where, $params, $this);
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
            self::$_db->createCollection(static::$realm, static::$tableName, $newObjectFieldData, static::$autoIncrementFieldName, true);
        }
    }

    public function delete() {
        self::$_db->deleteCollection(static::$realm, static::$tableName, $this->getPrimaryKeyData());
    }

    public function getPrimaryKeyData() {
        $data = array();

        foreach($this as $obj) {
            /** @var $obj NormBaseObject */
            $data[] = $obj->getPrimaryKeyData();
        }

        return $data;
    }

    public function getArray() {
        $arr = array();

        foreach($this as $id => $obj) {
            $arr[$id] = $obj->getArray();
        }

        return $arr;
    }

    public function getJson() {
        return json_encode($this->getArray());
    }

    public function loadByArray($arr) {
        foreach($arr as $id => $objArray) {
            $obj = new static::$singularClassName();
            $obj->loadByArray($objArray);
            $this[$id] = $obj;
        }
    }

    public function loadByJson($json) {
        $this->loadByArray(json_decode($json));
    }

    public static function getByPks(array $pks) {
        $coll = new static();
        $coll->loadByIds($pks);
        return $coll;
    }

    protected static function getFromSearchResults($results) {
        $coll = new static();
        $hits = $results['hits']['hits'];

        foreach($hits as $hit) {
            $source = $hit['_source'];
            $obj = new static::$singularClassName();
            $obj->loadByJson($source);
            $coll[$obj->getIdentifier()] = $obj;
        }

        return $coll;
    }

} 