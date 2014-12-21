<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CustomerBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'mysql';

    /** @var  string */
    public static $tableName = 'customer';

    /** @var string[] */
    public static $fieldNames = array('person_id', 'dob');

    /** @var string[] */
    public static $fieldTypes = array('int', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('personId', 'dob');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('person_id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('personId');

    /** @var  string[] */
    public static $autoIncrementFieldName = '';

    /** @var  string[] */
    public static $autoIncrementPropertyName = '';

    /** @var  string[] */
    public static $autoGenerateFieldName = '';

    /** @var  string[] */
    public static $autoGeneratePropertyName = '';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = false;


    /** @var int */
    public $personId;

    /** @var \DateTime */
    public $dob;


    public function __construct() {
        parent::__construct();

    }





    /**
     * @param $pk
     * @return \NormTests\mysql\Customer
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\mysql\Customer
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\mysql\Customer
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}