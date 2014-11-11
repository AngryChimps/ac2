<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class LocationBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'location';

    /** @var string[] */
    protected static $fieldNames = array('location_key', 'company_key', 'name', 'address', 'directions', 'lat', 'long', 'photos', 'availabilities', 'flags', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'float', 'float', 'string[]', 'DateTime[]', 'AdFlag[]', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('locationKey', 'companyKey', 'name', 'address', 'directions', 'lat', 'long', 'photos', 'availabilities', 'flags', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('location_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('locationKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


    /** @var string */
    public $locationKey;

    /** @var string */
    public $companyKey;

    /** @var string */
    public $name;

    /** @var string */
    public $address;

    /** @var string */
    public $directions;

    /** @var float */
    public $lat;

    /** @var float */
    public $long;

    /** @var string[] */
    public $photos;

    /** @var DateTime[] */
    public $availabilities;

    /** @var AdFlag[] */
    public $flags;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;


    /** @returns Norm\riak\Company */
    public function getCompany_() {
        if($this->Company_ === null) {
            $this->loadCompany_();
        }
        return $this->Company_;
    }


    protected function loadCompany_() {
        parent::loadProperty('Company_', 'company', 'key');
    }




    /**
     * @param $pk
     * @return Location
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Location
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Location
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}