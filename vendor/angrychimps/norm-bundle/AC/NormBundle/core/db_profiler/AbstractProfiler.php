<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 4/24/14
 * Time: 1:18 PM
 */

namespace AC\NormBundle\core\db_profiler;


class AbstractProfiler {
    abstract public function getTableNames($db);
    abstract public function getForeignKeys($db);
    abstract public function getFieldData($db, $tableName);
} 