<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ZipcodeBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'zipcode';

    /** @var string[] */
    public static $fieldNames = array('id', 'city', 'state', 'lat', 'long', 'north_lat', 'south_lat', 'east_long', 'west_long', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('int', 'string', 'string', 'float', 'float', 'float', 'float', 'float', 'float', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'city', 'state', 'lat', 'long', 'northLat', 'southLat', 'eastLong', 'westLong', 'createdAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

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
    public $id;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var float */
    public $lat;

    /** @var float */
    public $long;

    /** @var float */
    public $northLat;

    /** @var float */
    public $southLat;

    /** @var float */
    public $eastLong;

    /** @var float */
    public $westLong;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }





    /**
     * @param $pk
     * @return \NormTests\riak\Zipcode
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Zipcode
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Zipcode
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}