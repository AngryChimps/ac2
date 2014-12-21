<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class AddressBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'mysql';

    /** @var  string */
    public static $tableName = 'address';

    /** @var string[] */
    public static $fieldNames = array('id', 'street', 'city', 'state', 'zip', 'zip4');

    /** @var string[] */
    public static $fieldTypes = array('int', 'string', 'string', 'string', 'int', 'int');

    /** @var  string[] */
    public static $propertyNames = array('id', 'street', 'city', 'state', 'zip', 'zip4');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    public static $autoIncrementFieldName = 'id';

    /** @var  string[] */
    public static $autoIncrementPropertyName = 'id';

    /** @var  string[] */
    public static $autoGenerateFieldName = '';

    /** @var  string[] */
    public static $autoGeneratePropertyName = '';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = true;


    /** @var int */
    public $id;

    /** @var string */
    public $street;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var int */
    public $zip;

    /** @var int */
    public $zip4;


    public function __construct() {
        parent::__construct();

    }



    /** @return NormTests\mysql\Person */
    public function getPersonCollection() {
        if($this->Person === null) {
            $this->loadPerson();
        }
        return $this->Person;
    }

    /** @return NormTests\mysql\Company */
    public function getCompanyCollection() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }


    protected function loadPersonCollection() {
        parent::loadPropertyCollection('Person', 'person', 'address_id', 'addressId');
    }

    protected function loadCompanyCollection() {
        parent::loadPropertyCollection('Company', 'company', 'address_id', 'addressId');
    }


    /**
     * @param $pk
     * @return \NormTests\mysql\Address
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\mysql\Address
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\mysql\Address
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}