<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 4/24/14
 * Time: 1:09 PM
 */

namespace AC\NormBundle\core\db_profiler;

use \AC\NormBundle\core\Norm;

class MySQL extends AbstractProfiler {

    public function getTableNames($db)
    {
        $sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db";
        $params = array('db' => $db);
        $result =  Norm::query($db, $sql, $params);
        return $result;
    }

    public function getTableData($db, $tableName) {

    }

    public function getFieldData($db, $tableName) {
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tableName';
        $params = array(':db' => $db, ':tableName' => $tableName);
    }

    public function getFieldNames($db, $tablename)
    {
        // TODO: Implement getFieldNames() method.
    }

    public function getForeignKeys($db)
    {
        // TODO: Implement getForeignKeys() method.
    }
}