<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/19/14
 * Time: 10:06 AM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\NormBaseCollection;

abstract class AbstractDatastore {
    protected $connection;

    public abstract function create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName);
    public abstract function read($realm, $tableName, $primaryKeys);
    public abstract function update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys);
    public abstract function delete ($realm, $tableName, $primaryKeys);
    public abstract function createCollection($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName);
    public abstract function readCollection($realm, $tableName, $primaryKeys);
    public abstract function updateCollection($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys);
    public abstract function deleteCollection ($realm, $tableName, $primaryKeys);

    public abstract function readBySql($sql, $params);
    public abstract function readByWhere($tableName, $where, $params);
    public abstract function readCollectionBySql($sql, $params);
    public abstract function readCollectionByWhere($tableName, $where, $params);

    public abstract function query($sql, $params = array());
    public abstract function queryOneValue($sql, $params = array());
    public abstract function queryOneColumn($sql, $params = array());

    public abstract function getDbName();
}