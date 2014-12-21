<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:23 PM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\exceptions\UnsupportedObjectType;
use AC\NormBundle\core\Utils;
use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

abstract class AbstractPdoDatastore extends AbstractDatastore {
    /**
     * @var \PDO
     */
    protected $_connection;
    protected $_dbname;


    public function __construct($configParams, RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        parent::__construct($realmInfo, $loggerService);

        $this->_connection = new \PDO('mysql:dbname=' . $configParams['db_name'] . ';host=' . $configParams['host'] . ';port='
            . $configParams['port'], $configParams['user'], $configParams['password']);
        $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_dbname = $configParams['db_name'];
    }

    public function createObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        //Deal with times if necessary
        if (property_exists($obj, 'createdAt')) {
            $obj->createdAt = new \DateTime();
        }

        $data = $this->getAsArray($obj);

        //json encode any fields which need it and prepare params array
        $params = array();
        foreach($data as $fieldName => &$value) {
            if(is_array($value)) {
                $value = json_encode($value);
            }
            $params[':' . $fieldName] = $value;
        }

        $sql = 'INSERT INTO ' . $tableInfo['name'] . ' VALUES ( :';
        $sql .= implode(', :', array_keys($data));
        $sql .= ' );';

        if($debug !== null) {
            $arr = [];
            $arr['sql'] = $sql;
            $arr['params'] = $params;
            $debug['createObject'][] = $arr;
            $this->loggerService->info('Creating object: ' . json_encode($debug));
        }

        $this->query($sql, $params);

        if(!empty($tableInfo['autoIncrementProperty'])) {
            $obj->{$tableInfo['autoIncrementProperty']} = $this->_connection->lastInsertId();
        }
    }

    public function createCollection($coll, &$debug)
    {
        //This can be optimized to a single insert rather than one per object
        for($i = 0; $i < count($coll); $i++) {
            $this->createObject($coll[$i], $debug);
        }
    }

    public function updateObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        //Deal with times if necessary
        if (property_exists($obj, 'updatedAt')) {
            $obj->updatedAt = new \DateTime();
        }

        $data = $this->getAsArray($obj);

        //json encode any fields which need it and prepare params array
        $params = array();
        foreach($data as $fieldName => &$value) {
            if (!in_array($fieldName, $tableInfo['primaryKeyFieldNames'])) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                elseif(is_object($value) && get_class($value) === "\\DateTime") {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $params[':' . $fieldName] = $value;
            }
        }

        $sql = 'UPDATE ' . $tableInfo['name'] . ' SET  ';
        for($i = 0; $i < count($params); $i++) {
            if($i > 0) {
                $sql .= ', ';
            }
            $sql .= ltrim(array_keys($params)[$i], ':') . '=' . array_keys($params)[$i];
        }

        $sql .= ' WHERE ';
        for($i = 0; $i < count($tableInfo['primaryKeyFieldNames']); $i++) {
            if($i > 0) {
                $sql .= ' AND ';
            }
            $sql .= $tableInfo['primaryKeyFieldNames'][$i] . '=:' . $tableInfo['primaryKeyFieldNames'][$i];
            $params[':' . $tableInfo['primaryKeyFieldNames'][$i]] = $obj->$tableInfo['primaryKeyPropertyNames'][$i];
        }

        if($debug !== null) {
            $arr = [];
            $arr['sql'] = $sql;
            $arr['params'] = $params;
            $debug['updateObject'][] = $arr;
            $this->loggerService->info('Updating object: ' . json_encode($debug));
        }

        $this->query($sql, $params);
    }

    public function updateCollection($coll, &$debug)
    {
        for($i = 0; $i < count($coll); $i++) {
            $this->updateObject($coll[$i], $debug);
        }
    }

    public function deleteObject($obj, &$debug)
    {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $params = array();
        $sql = 'DELETE FROM ' . $tableInfo['name'] . ' WHERE ';

        for($i = 0; $i < count($tableInfo['primaryKeyFieldNames']); $i++) {
            if(count($params) > 0) {
                $sql .= ' AND ';
            }

            $sql .= $tableInfo['primaryKeyFieldNames'][$i] . ' = :' . $tableInfo['primaryKeyFieldNames'][$i];
            $params[':' . $tableInfo['primaryKeyFieldNames'][$i]] = $obj->{$tableInfo['primaryKeyPropertyNames'][$i]};
        }

        if($debug !== null) {
            $arr = [];
            $arr['sql'] = $sql;
            $arr['params'] = $params;
            $debug['deleteObject'][] = $arr;
            $this->loggerService->info('Deleting object: ' . json_encode($debug));
        }

        $this->query($sql, $params);
    }

    public function deleteCollection($coll, &$debug)
    {
        //This could be made more efficient using a WHERE IN clause
        for($i = 0; $i < count($coll); $i++) {
            $this->deleteObject($coll[$i], $debug);
        }
    }

    protected function getAsArray($obj) {
        if(is_array($obj)) {
            return $obj;
        }

        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $arr = [];

        if($this->isCollection($obj)) {
            foreach($obj as $object) {
                $arr[] = $this->getAsArray($object);
            }
        }
        else {
            for ($i = 0; $i < count($tableInfo['fieldNames']); $i++) {
                if ($obj->{$tableInfo['propertyNames'][$i]} === null) {
                    $arr[$tableInfo['fieldNames'][$i]] = null;
                } else {
                    switch ($tableInfo['fieldTypes'][$i]) {
                        case 'Date':
                            $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]}->format('Y-m-d');
                            break;
                        case 'DateTime':
                            $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]}->format('Y-m-d H:i:s');
                            break;
                        default:
                            if (class_exists($tableInfo['fieldTypes'][$i])) {
                                $arr[$tableInfo['fieldNames'][$i]] = $this->getAsArray($obj->{$tableInfo['propertyNames'][$i]});
                            } else {
                                $arr[$tableInfo['fieldNames'][$i]] = $obj->{$tableInfo['propertyNames'][$i]};
                            }
                    }
                }
            }
        }

        return $arr;
    }

//    public function saveObject(NormBaseObject $obj, &$debug = null)
//    {
//        //Deal with times if necessary
//        if (property_exists($obj, 'createdAt') && $obj->createdAt === null) {
//            $obj->createdAt = new \DateTime();
//        }
//        if (property_exists($obj, 'updatedAt')) {
//            $obj->updatedAt = new \DateTime();
//        }
//
//        $data = $this->getAsArray($obj);
//
//        //json encode any fields which need it and prepare params array
//        $params = array();
//        foreach($data as $fieldName => &$value) {
//            if(is_array($value)) {
//                $value = json_encode($value);
//            }
//            $params[':' . $fieldName] = $value;
//        }
//
//        if(!$obj->hasBeenPersisted) {
//            $sql = 'INSERT INTO ' . $obj::$tableName . ' VALUES ( :';
//            $sql .= implode(', :', array_keys($data));
//            $sql .= ' );';
//        }
//        else {
//            $sql = 'UPDATE ' . $obj::$tableName . ' SET  ';
//            foreach($params as $paramName => $value) {
//                $sql .= ltrim($paramName, ':') . '=' . $paramName;
//            }
//            $sql .= ' WHERE ';
//            for($i = 0; $i < count($obj::$primaryKeyFieldNames); $i++) {
//                if($i > 0) {
//                    $sql .= ' AND ';
//                }
//                $sql .= $obj::$primaryKeyFieldNames[$i] . '=' . $obj::$primaryKeyFieldNames[$i];
//            }
//        }
//
//
//
//        $this->query($sql, $params, $debug);
//
//        if($obj->isNewObject() && !empty($obj::$autoIncrementPropertyName)) {
//            $obj->{$obj::$autoIncrementPropertyName} = $this->_connection->lastInsertId();
//        }
//
//        $obj->hasBeenPersisted = true;
//    }

//    public function saveCollection(NormBaseCollection $coll, &$debug = null)
//    {
//        //This can later be optimized to make one large query inserting multiple objects
//        foreach($coll as $key => $object) {
//            $this->save($object, $debug);
//        }
//    }
//
//    public function deleteObject(NormBaseObject $obj, &$debug = null)
//    {
//        $pks = $this->getIdentifier($obj);
//
//
//        $params = array();
//        $sql = 'DELETE FROM ' . $obj::$tableName . ' WHERE ';
//        foreach($pks as $keyName => $value) {
//            if(count($params) > 0) {
//                $sql .= ' AND ';
//            }
//            $sql .= $keyName . ' = :' . $keyName;
//            $params[':' . $keyName] = $value;
//        }
//
//        $this->query($sql, $params);
//    }
//
//    public function deleteCollection(NormBaseCollection $coll, &$debug = null)
//    {
//        //This can be optimized later to a DELETE WHERE IN statement
//        foreach($coll as $key => $object) {
//            $this->delete($object, $debug);
//        }
//    }
//
    public function populateObjectByPks($obj, $pks, &$debug) {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));

        if(!is_array($pks)) {
            $pks = [$pks];
        }

        $sql = 'SELECT * FROM ' . $tableInfo['name'] . ' WHERE ';
        $params = array();

        for($i = 0; $i < count($pks); $i++) {
            if($i > 0) {
                $sql .= ' AND ';
            }
            $params[':' . $tableInfo['primaryKeyFieldNames'][$i]] = array_values($pks)[$i];
            $sql .= $tableInfo['primaryKeyFieldNames'][$i] . '=:' . $tableInfo['primaryKeyFieldNames'][$i];
        }

        $this->populateObjectBySql($obj, $sql, $params, $debug);
    }

    public function populateObjectByWhere($obj, $where, $params, &$debug) {
        $tableInfo = $this->realmInfo->getTableInfo(get_class($obj));
        $sql = 'SELECT * FROM ' . $tableInfo['name'] . ' WHERE ' . $where;

        $this->populateObjectBySql($obj, $sql, $params, $debug);
    }

    public function populateObjectBySql($obj, $sql, $params, &$debug) {
        if($debug !== null) {
            $arr = [];
            $arr['sql'] = $sql;
            $arr['params'] = $params;
            $debug['populateObjectBySql'][] = $arr;
            $this->loggerService->info('Populating object by SQL: ' . json_encode($debug));
        }

        $stmt = $this->query($sql, $params);

        if($stmt->rowCount() == 0) {
            return false;
        }
        elseif($stmt->rowCount() > 1) {
            throw new \Exception('Multiple objects of type ' . get_class($obj) . ' were found (SQL: ' . $sql . '; params: ' . json_encode($params) . ')');
        }

        while($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $this->populateObjectWithArray($obj, $row);
        }

        return true;
    }

    public function populateCollectionByPks($coll, $pks, &$debug) {
        //For a collection $pks would be an array of ids or an array of an array of ids
        //This can also be optimized to a WHERE IN clause later
        $tableInfo = $this->realmInfo->getTableInfo(get_class($coll));

        foreach($pks as $pk) {
            $object = new $tableInfo['objectName']();

            if($this->populateObjectByPks($object, $pk, $debug) === false) {
                throw new \Exception('Unable to find one or more objects to populate the collection.');
            }

            $coll[$this->getIdentifier($object)] = $object;
        }
    }

//    protected function populateObjectByOrderedArray($obj, array $arr) {
//        if($obj instanceof NormBaseObject) {
//            for($i = 0; $i < count($obj::$fieldNames); $i++) {
//                $fieldType = $obj::$fieldTypes[$i];
//                $propertyName = $obj::$propertyNames[$i];
//                $value = $arr[$i];
//
//                if(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseObject", class_parents($fieldType))) {
//                    $object = new $fieldType();
//                    $this->populateObjectByOrderedArray($object, json_decode($value));
//                    $obj->$propertyName = $object;
//                }
//                elseif(class_exists($fieldType) && in_array("AC\\NormBundle\\core\\NormBaseCollection", class_parents($fieldType))) {
//                    $object = new $fieldType();
//                    $this->populateObjectByOrderedArray($object, json_decode($value));
//                    $obj->$propertyName = $object;
//                }
//                else {
//                    switch($fieldType) {
//                        case 'int':
//                            $obj->$propertyName = (int) $value;
//                            break;
//                        case 'bool':
//                            $obj->$propertyName = (bool) $value;
//                            break;
//                        case 'float':
//                            $obj->$propertyName = (float) $value;
//                            break;
//                        case 'Date':
//                        case 'DateTime':
//                            $obj->$propertyName = new \DateTime($value);
//                            break;
//                        case 'int[]':
//                        case 'float[]':
//                        case 'double[]':
//                        case 'string[]':
//                            $obj->$propertyName = $value;
//                            break;
//                        default:
//                            $obj->$propertyName = $value;
//                    }
//                }
//
//            }
//        }
//        elseif($obj instanceof NormBaseCollection) {
//            foreach($arr as $objData) {
//                $singularClass = $obj::$singularClassName;
//                $object = $singularClass();
//                $this->populateObjectByOrderedArray($object, $objData);
//                $obj->{$this->getIdentifier($object)} = $object;
//            }
//        }
//        else {
//            throw new UnsupportedObjectType(get_class($obj));
//        }
//    }
//
//    protected function getAsArray($obj) {
//        $data = array();
//        if($obj instanceof NormBaseObject) {
//            for($i=0; $i < count($obj::$fieldNames); $i++) {
//                $data[$obj::$fieldNames[$i]] = $this->getAsArray($obj->{$obj::$propertyNames[$i]});
//            }
//            return $data;
//        }
//        if($obj instanceof NormBaseCollection) {
//            foreach($obj as $id => $object) {
//                $data[$id] = $this->getAsArray($object);
//            }
//            return $data;
//        }
//        if($obj instanceof \DateTime) {
//            return $obj->format('Y-m-d H:i:s');
//        }
//        else {
//            return $obj;
//        }
//
//    }
//
//    public function getIdentifier(NormBaseObject $obj) {
//        $pks = array();
//        for($i = 0; $i<count($obj::$primaryKeyFieldNames); $i++) {
//            $pks[] = $obj->{$obj::$primaryKeyPropertyNames[$i]};
//        }
//
//        return $this->getKeyName($pks);
//    }
//
//    protected function getKeyName($primaryKeys) {
//        foreach($primaryKeys as &$primaryKey) {
//            if($primaryKey instanceof \DateTime) {
//                $primaryKey = $primaryKey->format('Y-m-d H:i:s');
//            }
//        }
//        return implode('|', $primaryKeys);
//    }
//
//
    /**
     * @param $sql
     * @param array $params
     * @param &array $debug
     * @return \PDOStatement
     */
    public function query($sql, $params = array()) {
        $dbh =  $this->_connection;

        foreach($params as &$value) {
            if($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
        }
        $stmt =$dbh->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
//
//    public function queryOneValue($sql, $params = array()) {
//        $stmt = self::query($sql, $params);
//        $result = $stmt->fetchAll(\PDO::FETCH_NUM);
//
//        if(count($result) != 1) {
//            throw new OneValueQueryException($this->connection, $sql, $params, $result->rowCount());
//        }
//
//        return $result[0][0];
//    }
//
//
//    public function queryOneColumn($sql, $params = array()) {
//        $return = array();
//
//        $stmt = self::query($sql, $params);
//
//        while($row = $stmt->fetch(\PDO::FETCH_NUM)) {
//            $return[] = $row[0];
//        }
//
//        return $return;
//    }
//
////    public function create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
////    {
////        $params = array();
////        foreach($fieldData as $fieldName => $value) {
////            if($value instanceof \DateTime) {
////                $params[':' . $fieldName] = $value->format('Y-m-d H:i:s');
////            }
////            else {
////                $params[':' . $fieldName] = $value;
////                }
////        }
////
////        $sql = 'INSERT INTO ' . $tableName . ' VALUES ( :';
////        $sql .= implode(', :', array_keys($fieldData));
////        $sql .= ' );';
////
////        $this->query($sql, $params);
////
////        return (!empty($autoIncrementFieldName) ? $this->_connection->lastInsertId() : null);
////    }
////
////    public function read($realm, $tableName, $primaryKeys)
////    {
////        $params = array();
////        $where = '';
////        foreach($primaryKeys as $keyName => $value) {
////            if(count($params) > 0) {
////                $where .= ' AND ';
////            }
////            $where .= $keyName . ' = :' . $keyName;
////            $params[':' . $keyName] = $value;
////        }
////
////        return $this->readByWhere($tableName, $where, $params, $primaryKeys);
////    }
////
////    public function readBySql($sql, $params)
////    {
////        $arr = array();
////        $stmt = $this->query($sql, $params);
////        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
////            foreach($row as $field=>$value) {
////                $arr[$field] = $value;
////            }
////        }
////
////        return $arr;
////    }
////
////    public function readByWhere($tableName, $where, $params)
////    {
////        $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;
////
////        return $this->readBySql($sql, $params);
////    }
////
////    public function update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
////    {
////        $params = array();
////        $sql = 'UPDATE ' . $tableName . ' SET ';
////
////        foreach($fieldDataWithoutPrimaryKeys as $keyName => $value) {
////            if(count($params) > 0) {
////                $sql .= ', ';
////            }
////            $sql .= $keyName . ' = :' . $keyName;
////            $params[':' . $keyName] = $value;
////        }
////
////        $sql .= ' WHERE ';
////
////        $i=0;
////        foreach($primaryKeys as $keyName => $value) {
////            if($i > 0) {
////                $sql .= ' AND ';
////            }
////            $sql .= $keyName . ' = :' . $keyName;
////            $params[':' . $keyName] = $value;
////        }
////
////        $this->query($sql, $params);
////    }
////
////
////    public function createCollection($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
////    {
////        if(!empty($autoIncrementFieldName)) {
////            $ids = array();
////
////            for($i=0; $i<count($fieldData); $i++) {
////                $ids[] = $this->create($realm, $tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
////            }
////
////            return $ids;
////        }
////        else {
////            $params = array();
////            foreach($fieldData as $row) {
////                foreach($row as $fieldName => $value) {
////                    $params[':' . $fieldName . '_ '. count($params)] = $value;
////                }
////            }
////
////            $sql = 'INSERT INTO ' . $tableName . ' VALUES ( ';
////
////            foreach ($fieldData as $row) {
////                $sql .= '(' . implode(', ', array_values($row)) . '), ';
////            }
////
////            rtrim($sql, ',');
////
////            $sql .= ' );';
////
////            $this->query($sql, $params);
////        }
////    }
////
////    public function readCollection($realm, $tableName, $primaryKeys)
////    {
////        //If there is only a signle primary key in the table, we can use a WHERE IN clause
////        if(count($primaryKeys[0]) === 1) {
////            $params = array();
////            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $primaryKeys[0][0] . ' IN ( ';
////            for($i=0; $i < count($primaryKeys); $i++) {
////                if(count($params) > 0) {
////                    $sql .= ', ';
////                }
////                $sql .= $primaryKeys[0][0] . ' = :' . $primaryKeys[0][0];
////                $params[':' . $primaryKeys[0][0]] = $primaryKeys[0];
////            }
////            $sql .= ' )';
////
////            $stmt = $this->query($sql, $params);
////            return $stmt->fetchAll();
////        }
////        else {
////            for($i=0; $i < count($primaryKeys); $i++) {
////                return $this->read($realm, $tableName, $primaryKeys[$i]);
////            }
////        }
////    }
////
////    public function readCollectionBySql($sql, $params) {
////        $stmt = $this->query($sql, $params);
////        return $stmt->fetchAll();
////    }
////
////    public function readCollectionByWhere($tableName, $where, $params) {
////        $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;
////        $this->readCollectionBySql($sql, $params);
////    }
//
//
////    public function updateCollection($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
////    {
////        for($i=0; $i < count($primaryKeys); $i++) {
////            $this->update($realm, $tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys);
////        }
////    }
//
////    public function deleteCollection($realm, $tableName, $primaryKeys)
////    {
////        //If there is only a signle primary key in the table, we can use a WHERE IN clause
////        if(count($primaryKeys[0]) === 1) {
////            $params = array();
////            $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $primaryKeys[0][0] . ' IN ( ';
////            for($i=0; $i < count($primaryKeys); $i++) {
////                if(count($params) > 0) {
////                    $sql .= ', ';
////                }
////                $sql .= $primaryKeys[0][0] . ' = :' . $primaryKeys[0][0];
////                $params[':' . $primaryKeys[0][0]] = $primaryKeys[0];
////            }
////            $sql .= ' )';
////
////            $stmt = $this->query($sql, $params);
////        }
////        else {
////            for($i=0; $i < count($primaryKeys); $i++) {
////                $this->delete($realm, $tableName, $primaryKeys[$i]);
////            }
////        }
////    }
//
//    protected function connect() {
//        $this->_connection = new \PDO('mysql:host=' . $this->config['hostname'] . ';dbname=' . $this->config['databaseName'],
//            $this->config['username'], $this->config['password'],
//            ($this->config['persistent'] == true) ? \PDO::ATTR_PERSISTENT : null);
//
//        $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
//    }
//
//    public function getConnection() {
//        return $this->_connection;
//    }
}