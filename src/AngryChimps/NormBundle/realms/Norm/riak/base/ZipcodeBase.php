<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ZipcodeBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'zipcode';

    /** @var string[] */
    protected static $fieldNames = array('id', 'city', 'state', 'lat', 'long', 'north_lat', 'south_lat', 'east_long', 'west_long', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'float', 'float', 'float', 'float', 'float', 'float', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'city', 'state', 'lat', 'long', 'northLat', 'southLat', 'eastLong', 'westLong', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = '';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


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

    /** @var DateTime */
    public $createdAt;






    /**
     * @param $pk
     * @return \Norm\riak\Zipcode
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Zipcode
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Zipcode
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}