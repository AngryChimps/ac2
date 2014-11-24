<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/18/14
 * Time: 1:23 PM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\core\Norm;
use AC\NormBundle\core\NormBaseObject;
use AC\NormBundle\core\Utils;

abstract class AbstractPdoDatastore extends AbstractDatastore {
    /**
     * @var \PDO
     */
    protected $_connection;
    protected $_dbname;

    public function __construct($configParams) {
        $this->_connection = new \PDO('mysql:dbname=' . $configParams['db_name'] . ';host=' . $configParams['host'] . ';port='
            . $configParams['port'], $configParams['user'], $configParams['password']);
        $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_dbname = $configParams['db_name'];
    }

    public function getDbName() {
        return $this->_dbname;
    }

    /**
     * @param $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = array()) {
        $dbh =  $this->_connection;

        $stmt =$dbh->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function queryOneValue($sql, $params = array()) {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetchAll(\PDO::FETCH_NUM);

        if(count($result) != 1) {
            throw new OneValueQueryException($this->connection, $sql, $params, $result->rowCount());
        }

        return $result[0][0];
    }


    public function queryOneColumn($sql, $params = array()) {
        $return = array();

        $stmt = self::query($sql, $params);

        while($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $return[] = $row[0];
        }

        return $return;
    }

    public function create($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        $params = array();
        foreach($fieldData as $fieldName => $value) {
            $params[':' . $fieldName] = $value;
        }

        $sql = 'INSERT INTO ' . $tableName . ' VALUES ( :';
        $sql .= implode(', :', array_keys($fieldData));
        $sql .= ' );';

        $this->query($sql, $params);

        return (!empty($autoIncrementFieldName) ? $this->_connection->lastInsertId() : null);
    }

    public function read($realm, $tableName, $primaryKeys)
    {
        $params = array();
        $where = '';
        foreach($primaryKeys as $keyName => $value) {
            if(count($params) > 0) {
                $where .= ' AND ';
            }
            $where .= $keyName . ' = :' . $keyName;
            $params[':' . $keyName] = $value;
        }

        return $this->readByWhere($tableName, $where, $params, $primaryKeys);
    }

    public function readBySql($sql, $params)
    {
        $arr = array();
        $stmt = $this->query($sql, $params);
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            foreach($row as $field=>$value) {
                $arr[$field] = $value;
            }
        }

        return $arr;
    }

    public function readByWhere($tableName, $where, $params)
    {
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;

        return $this->readBySql($sql, $params);
    }

    public function update($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        $params = array();
        $sql = 'UPDATE ' . $tableName . ' SET ';

        foreach($fieldDataWithoutPrimaryKeys as $keyName => $value) {
            if(count($params) > 0) {
                $sql .= ', ';
            }
            $sql .= $keyName . ' = :' . $keyName;
            $params[':' . $keyName] = $value;
        }

        $sql .= ' WHERE ';

        $i=0;
        foreach($primaryKeys as $keyName => $value) {
            if($i > 0) {
                $sql .= ' AND ';
            }
            $sql .= $keyName . ' = :' . $keyName;
            $params[':' . $keyName] = $value;
        }

        $this->query($sql, $params);
    }

    public function delete($realm, $tableName, $primaryKeys)
    {
        $params = array();
        $sql = 'DELETE FROM ' . $tableName . ' WHERE ';
        foreach($primaryKeys as $keyName => $value) {
            if(count($params) > 0) {
                $sql .= ' AND ';
            }
            $sql .= $keyName . ' = :' . $keyName;
            $params[':' . $keyName] = $value;
        }

        $this->query($sql, $params);
    }

    public function createCollection($realm, $tableName, $fieldData, $primaryKeys, $autoIncrementFieldName)
    {
        if(!empty($autoIncrementFieldName)) {
            $ids = array();

            for($i=0; $i<count($fieldData); $i++) {
                $ids[] = $this->create($realm, $tableName, $fieldData[$i], $primaryKeys[$i], $autoIncrementFieldName);
            }

            return $ids;
        }
        else {
            $params = array();
            foreach($fieldData as $row) {
                foreach($row as $fieldName => $value) {
                    $params[':' . $fieldName . '_ '. count($params)] = $value;
                }
            }

            $sql = 'INSERT INTO ' . $tableName . ' VALUES ( ';

            foreach ($fieldData as $row) {
                $sql .= '(' . implode(', ', array_values($row)) . '), ';
            }

            rtrim($sql, ',');

            $sql .= ' );';

            $this->query($sql, $params);
        }
    }

    public function readCollection($realm, $tableName, $primaryKeys)
    {
        //If there is only a signle primary key in the table, we can use a WHERE IN clause
        if(count($primaryKeys[0]) === 1) {
            $params = array();
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $primaryKeys[0][0] . ' IN ( ';
            for($i=0; $i < count($primaryKeys); $i++) {
                if(count($params) > 0) {
                    $sql .= ', ';
                }
                $sql .= $primaryKeys[0][0] . ' = :' . $primaryKeys[0][0];
                $params[':' . $primaryKeys[0][0]] = $primaryKeys[0];
            }
            $sql .= ' )';

            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        }
        else {
            for($i=0; $i < count($primaryKeys); $i++) {
                return $this->read($realm, $tableName, $primaryKeys[$i]);
            }
        }
    }

    public function readCollectionBySql($sql, $params) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function readCollectionByWhere($tableName, $where, $params) {
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;
        $this->readCollectionBySql($sql, $params);
    }


    public function updateCollection($realm, $tableName, $primaryKeys, $fieldDataWithoutPrimaryKeys)
    {
        for($i=0; $i < count($primaryKeys); $i++) {
            $this->update($realm, $tableName, $primaryKeys[$i], $fieldDataWithoutPrimaryKeys);
        }
    }

    public function deleteCollection($realm, $tableName, $primaryKeys)
    {
        //If there is only a signle primary key in the table, we can use a WHERE IN clause
        if(count($primaryKeys[0]) === 1) {
            $params = array();
            $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . $primaryKeys[0][0] . ' IN ( ';
            for($i=0; $i < count($primaryKeys); $i++) {
                if(count($params) > 0) {
                    $sql .= ', ';
                }
                $sql .= $primaryKeys[0][0] . ' = :' . $primaryKeys[0][0];
                $params[':' . $primaryKeys[0][0]] = $primaryKeys[0];
            }
            $sql .= ' )';

            $stmt = $this->query($sql, $params);
        }
        else {
            for($i=0; $i < count($primaryKeys); $i++) {
                $this->delete($realm, $tableName, $primaryKeys[$i]);
            }
        }
    }

    protected function connect() {
        $this->_connection = new \PDO('mysql:host=' . $this->config['hostname'] . ';dbname=' . $this->config['databaseName'],
            $this->config['username'], $this->config['password'],
            ($this->config['persistent'] == true) ? \PDO::ATTR_PERSISTENT : null);

        $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection() {
        return $this->_connection;
    }

    public function getClassConfigs($realm) {
        throw new \Exception('This needs to be implemented');
    }
}