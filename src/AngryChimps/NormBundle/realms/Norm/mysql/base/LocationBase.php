<?php
namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class LocationBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'location';

    /** @var string[] */
    protected static $fieldNames = array('id', 'key', 'company_id', 'name', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'directions', 'lat', 'long', 'photos', 'availabilities', 'flags', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'float', 'float', 'string[]', 'DateTime[]', 'AdFlag[]', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'key', 'companyId', 'name', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'directions', 'lat', 'long', 'photos', 'availabilities', 'flags', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    protected static $autoIncrementFieldName = 'id';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = 'id';

    /** @var  string[] */
    protected static $autoGenerateFieldName = '';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = true;


    /** @var int */
    public $id;

    /** @var string */
    public $key;

    /** @var string */
    public $companyId;

    /** @var string */
    public $name;

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


    /** @returns Norm\mysql\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }


    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }




    /**
     * @param $pk
     * @return \Norm\mysql\Location
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\mysql\Location
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\mysql\Location
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}