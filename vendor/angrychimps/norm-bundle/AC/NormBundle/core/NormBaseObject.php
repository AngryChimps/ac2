<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 4/24/14
 * Time: 12:32 PM
 */

namespace AC\NormBundle\core;

use \AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\datastore\DatastoreManager;
use \AC\NormBundle\config\Config;
use \AC\NormBundle\core\exceptions\CannotChangePrimaryKeyException;
use AC\NormBundle\core\exceptions\ObjectAlreadyDeletedException;
use Symfony\Component\DependencyInjection\ContainerAware;


abstract class NormBaseObject extends ContainerAware {
    protected $_originalPropertyData = array();

    /**
     * @var AbstractDatastore
     */
    protected $db;

    /**
     * @var AbstractDatastore
     */
    protected $cache;

    private $_hasBeenPersisted = false;
    private $_hasBeenDeleted = false;

    protected static $realm;
    protected static $primaryDatastoreName;
    protected static $cacheDatastoreName;
    protected static $tableName;
    protected static $fieldNames;
    protected static $fieldTypes;
    protected static $propertyNames;
    protected static $primaryKeyPropertyNames;
    protected static $primaryKeyFieldNames;
    protected static $autoIncrementFieldName;
    protected static $autoIncrementPropertyName;
    protected static $autoGenerateFieldName;
    protected static $autoGeneratePropertyName;

    public function __construct() {
        $this->db = DatastoreManager::getDatastore(static::$primaryDatastoreName);

        if(!empty(static::$autoGenerateFieldName)) {
            if(isset($_SERVER['SERVER_ADDR'])) {
                $seed = microtime(true) . $_SERVER['SERVER_ADDR'] . $_SERVER['REMOTE_ADDR'];
            }
            else {
                $seed = microtime(true) . 'commandline';
            }
            $hash = md5($seed);
            $this->{static::$autoGeneratePropertyName} = substr($hash, 0, 16);
        }
    }

    public function loadFromArray($array = array()) {
        if($this->_hasBeenDeleted) {
            throw new ObjectAlreadyDeletedException(static::$tableName, $this->getPrimaryKeyData());
        }
        foreach($array as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function getPrimaryKeyFieldNames() {
        return static::$primaryKeyFieldNames;
    }
    public function save() {
        if($this->_hasBeenDeleted) {
            throw new ObjectAlreadyDeletedException(static::$tableName, $this->getPrimaryKeyData());
        }

        //Set created_at updated_at datetimes
        $this->updateDateTimes();

        if($this->_hasBeenPersisted) {
            $this->checkPrimaryKeyValuesHaveNotChanged();
            $this->updateHook(static::$realm, static::$tableName, $this->getPrimaryKeyData(), $this->getFieldDataWithoutPrimaryKeys());
        }
        else {
            if(empty(static::$autoIncrementPropertyName)) {
                $this->createHook(static::$realm, static::$tableName, $this->getFieldData(), $this->getPrimaryKeyData(),
                    static::$autoIncrementPropertyName);
            }
            else {
                $id = $this->createHook(static::$realm, static::$tableName, $this->getFieldData(),
                    $this->getPrimaryKeyData(), static::$autoIncrementPropertyName);
                $this->{static::$autoIncrementPropertyName} = (int) $id;
            }
            $this->_hasBeenPersisted = true;
        }

        $this->updateOriginalValues();
    }

    protected function updateDateTimes() {
        if(in_array('created_at', static::$fieldNames) && $this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
        if($this->_hasBeenPersisted && in_array('updated_at', static::$fieldNames)) {
            $this->updatedAt = new \DateTime();
        }
    }

    protected function updateOriginalValues() {
        foreach(static::$propertyNames as $name) {
            $this->_originalPropertyData[$name] = $this->$name;
        }
    }

    protected function checkPrimaryKeyValuesHaveNotChanged() {
        foreach(static::$primaryKeyPropertyNames as $pkName) {
            if(isset($this->_originalPropertyData[$pkName]) && $this->$pkName !== $this->_originalPropertyData[$pkName]) {
                throw new CannotChangePrimaryKeyException(static::$primaryDatastoreName, static::$tableName);
            }
        }
    }

    public function delete() {
        if(!$this->_hasBeenPersisted) {
            throw(new \Exception('Unable to delete an item which has not been persisted'));
        }

        $this->deleteHook(static::$realm, static::$tableName, $this->getPrimaryKeyData());
        $this->invalidate();
        $this->_hasBeenDeleted = true;
    }

    public static function getByPk($pk) {
        $pkData = array();
        $className = get_called_class();
        if(!is_array($pk)) {
            $pk = array($pk);
        }

        $obj = NormObjectLocalStore::get(static::$realm, $className, $pk);
        if($obj !== null) {
            return $obj;
        }

        for($i=0; $i<count(static::$primaryKeyFieldNames); $i++) {
            $pkData[static::$primaryKeyFieldNames[$i]] = $pk[$i];
        }

        $ds = DatastoreManager::getDatastore(static::$primaryDatastoreName);
        $data = $ds->read(static::$realm, static::$tableName, $pkData);

        if(empty($data)) {
            return null;
        }

        $obj = new $className();
        $obj->loadByFieldDataAssociativeArray($data);
        $obj->_hasBeenPersisted = true;
        $obj->updateOriginalValues();

        NormObjectLocalStore::add($obj);

        return $obj;
    }

    public static function getBySql($sql, $params = array()) {
        $className = get_called_class();
        $ds = DatastoreManager::getDatastore(static::$primaryDatastoreName);
        $data = $ds->readBySql($sql, $params);
        $obj = new $className();
        $obj->loadByFieldDataArray($data);
        $obj->_hasBeenPersisted = true;

        NormObjectLocalStore::add($obj);

        return $obj;
    }

    public static function getByWhere($where, $params = array()) {
        $className = get_called_class();
        $ds = DatastoreManager::getDatastore(static::$primaryDatastoreName);
        $data = $ds->readByWhere(static::$tableName, $where, $params);

        $obj = new $className();
        $obj->loadByFieldDataAssociativeArray($data);
        $obj->_hasBeenPersisted = true;

        NormObjectLocalStore::add($obj);

        return $obj;
    }

    public function invalidate() {
        NormObjectLocalStore::invalidate(self::$realm, get_called_class(), $this->getPrimaryKeyData());
    }

    public static function invalidateAll() {
        NormObjectLocalStore::invalidateAll();
    }

    public function loadByJson($json) {
        if($this->_hasBeenDeleted) {
            throw new ObjectAlreadyDeletedException(static::$tableName, $this->getPrimaryKeyData());
        }
        $array = json_decode($json);
        foreach($array as $k => $v) {
            $property = Utils::field2property($k);
            $this->$property = $v;
        }
    }

    private function loadFieldWithValue($fieldType, $propertyName, $value) {
        if(class_exists($fieldType) && $fieldType instanceof NormBaseObject) {
            $obj = new $fieldType();
            $obj->loadByJson($value);
            $this->$propertyName = $obj;
        }
        elseif(class_exists($fieldType) && $fieldType instanceof NormBaseCollection) {
            $obj = new $fieldType();
            $obj->loadByJson($value);
            $this->$propertyName = $obj;
        }
        else {
            switch($fieldType) {
                case 'int':
                    $this->$propertyName = (int) $value;
                    break;
                case 'bool':
                    $this->$propertyName = (bool) $value;
                    break;
                case 'float':
                    $this->$propertyName = (float) $value;
                    break;
                case 'Date':
                case 'DateTime':
                    $this->$propertyName = new \DateTime($value);
                break;
                default:
                    $this->$propertyName = $value;
            }
        }
    }

    public function loadByFieldDataFlatArray($arr)
    {
        if($this->_hasBeenDeleted) {
            throw new ObjectAlreadyDeletedException(static::$tableName, $this->getPrimaryKeyData());
        }
        if (is_array($arr)) {
            for ($i = 0; $i < count($arr); $i++) {
                $this->loadFieldWithValue(static::$fieldTypes[$i], static::$propertyNames[$i], $arr[$i]);
            }
        }
        else {
            for ($i = 0; $i < count($arr); $i++) {
                $this->loadFieldWithValue(static::$fieldTypes[$i], static::$propertyNames[$i], $arr->$i);
            }
        }
    }

    public function loadByFieldDataAssociativeArray($arr)
    {
        if($this->_hasBeenDeleted) {
            throw new ObjectAlreadyDeletedException(static::$tableName, $this->getPrimaryKeyData());
        }
        if (is_array($arr)) {
            for ($i = 0; $i < count($arr); $i++) {
                $this->loadFieldWithValue(static::$fieldTypes[$i], static::$propertyNames[$i], $arr[static::$fieldNames[$i]]);
            }
        }
        else {
            for ($i = 0; $i < count($arr); $i++) {
                $this->loadFieldWithValue(static::$fieldTypes[$i], static::$propertyNames[$i], $arr->{static::$fieldNames[$i]});
            }
        }
    }

    public function getPrimaryKeyData() {
        $data = array();

        for($i=0; $i<count(static::$primaryKeyFieldNames); $i++) {
            switch(static::$fieldTypes[$i]) {
                case 'Date':
                    $data[static::$primaryKeyFieldNames[$i]] = $this->{static::$primaryKeyPropertyNames[$i]}->format('Y-m-d');
                    break;
                case 'DateTime':
                    $data[static::$primaryKeyFieldNames[$i]] = $this->{static::$primaryKeyPropertyNames[$i]}->format('Y-m-d H:i:s');
                    break;
                default:
                    $data[static::$primaryKeyFieldNames[$i]] = $this->{static::$primaryKeyPropertyNames[$i]};
            }
        }

        return $data;
    }

    protected function getFieldDataWithoutPrimaryKeys() {
        $fieldNames = array_diff(static::$fieldNames, static::$primaryKeyFieldNames);
        $propertyNames = array_diff(static::$propertyNames, static::$primaryKeyPropertyNames);

        $arr = array();
        foreach($fieldNames as $index => $fieldName) {
            if($this->$propertyNames[$index] === null) {
                $arr[$fieldNames[$index]] = null;
            }
            else {
                switch(static::$fieldTypes[$index]) {
                    case 'DateTime':
                        $dt = $this->{$propertyNames[$index]};
                        $arr[$fieldNames[$index]] = $dt->format('Y-m-d H:i:s');
                        break;
                    case 'Date':
                        $d = $this->{$propertyNames[$index]};
                        $arr[$fieldNames[$index]] = $d->format('Y-m-d H:i:s');
                        break;
                    default:
                        $arr[$fieldNames[$index]] = $this->{$propertyNames[$index]};
                }
            }
        }
        return $arr;
    }

    protected function getChangedFields() {
        $changed = array();

        foreach(static::$propertyNames as $propertyName) {
            if($this->$propertyName !== $this->_originalPropertyData[$propertyName]) {
                $changed[$propertyName] = $this->$propertyName;
            }
        }

        return $changed;
    }

    protected function hasCache() {
        return ($this->_cache === NULL);
    }

    public function getRealm() {
        return static::$realm;
    }

    public function getFieldData() {
        $arr = array();
        for($i = 0; $i < count(static::$fieldNames); $i++) {
            if($this->{static::$propertyNames[$i]} === null) {
                $arr[static::$fieldNames[$i]] = null;
            }
            elseif($this->{static::$propertyNames[$i]} instanceof NormBaseObject) {
                $arr[static::$fieldNames[$i]] = $this->{static::$propertyNames[$i]}->getJson();
            }
            elseif($this->{static::$propertyNames[$i]} instanceof NormBaseCollection) {
                $arr[static::$fieldNames[$i]] = $this->{static::$propertyNames[$i]}->getJson();
            }
            else {
                switch(static::$fieldTypes[$i]) {
                    case 'DateTime':
                        $arr[static::$fieldNames[$i]] = $this->{static::$propertyNames[$i]}->format('Y-m-d H:i:s');
                        break;
                    case 'Date':
                        $arr[static::$fieldNames[$i]] = $this->{static::$propertyNames[$i]}->format('Y-m-d');
                        break;
                    default:
                        $arr[static::$fieldNames[$i]] = $this->{static::$propertyNames[$i]};
                }
            }
        }
        return $arr;
    }

    public function getJson() {
        return json_encode($this->getFieldData());
    }

    public function isNewObject() {
        return $this->_hasBeenPersisted;
    }

    public function hasAutoIncrement() {
        return (static::$autoIncrementFieldName !== NULL);
    }

    protected function loadProperty($propertyName, $remoteTableName, $localPropertyIdFieldName) {
        $remoteClassName = Utils::table2class($remoteTableName);
        $remoteObj = new $remoteClassName();
        $remoteObj->loadById($this->$localPropertyIdFieldName);
        $this->{$propertyName} = $remoteObj;
    }

    protected function loadPropertyCollection($propertyName, $remoteTableName, $remoteFieldName,
                                              $localPropertyIdFieldName) {
        $remoteClassName = Utils::table2class($remoteTableName) . 'Collection';
        $remoteObj = new $remoteClassName();
        $remoteObj->loadByWhere($remoteFieldName . ' = :fieldName', array(':fieldName' => $this->$localPropertyIdFieldName));
        $this->{$propertyName} = $remoteObj;
    }

    protected function createHook($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName) {
        return $this->db->create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName);
    }

//    protected function readHook($realm, $tableName, $primaryKeys) {
//        $ds = DatastoreManager::getDatastore(static::$primaryDatastoreName);
//        return $ds->read($realm, $tableName, $primaryKeys);
//    }

    protected function updateHook($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys) {
        return $this->db->update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys);
    }

    protected function deleteHook($realm, $tableName, $primaryKeys) {
        return $this->db->delete($realm, $tableName, $primaryKeys);
    }
}