<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class AddressBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'address';

    /** @var string[] */
    public static $fieldNames = array('id', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'lat', 'long', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'float', 'float', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'lat', 'long', 'createdAt', 'updatedAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    public static $autoIncrementFieldName = '';

    /** @var  string[] */
    public static $autoIncrementPropertyName = '';

    /** @var  string[] */
    public static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    public static $autoGeneratePropertyName = 'id';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = false;


    /** @var string */
    public $id;

    /** @var string */
    public $street1;

    /** @var string */
    public $street2;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var string */
    public $zip;

    /** @var string */
    public $phone;

    /** @var float */
    public $lat;

    /** @var float */
    public $long;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

    }





    /**
     * @param $pk
     * @return \NormTests\riak\Address
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Address
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Address
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}