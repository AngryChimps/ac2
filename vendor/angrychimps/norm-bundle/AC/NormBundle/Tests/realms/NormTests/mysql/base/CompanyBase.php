<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'mysql';

    /** @var  string */
    public static $tableName = 'company';

    /** @var string[] */
    public static $fieldNames = array('id', 'name', 'address_id');

    /** @var string[] */
    public static $fieldTypes = array('int', 'string', 'int');

    /** @var  string[] */
    public static $propertyNames = array('id', 'name', 'addressId');

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
    public $name;

    /** @var int */
    public $addressId;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\mysql\Address */
    public function getAddress() {
        if($this->Address === null) {
            $this->loadAddress();
        }
        return $this->Address;
    }


    protected function loadAddress() {
        parent::loadProperty('Address', 'address', 'id');
    }


    /** @return NormTests\mysql\CompanyPerson */
    public function getCompanyPersonCollection() {
        if($this->CompanyPerson === null) {
            $this->loadCompanyPerson();
        }
        return $this->CompanyPerson;
    }


    protected function loadCompanyPersonCollection() {
        parent::loadPropertyCollection('CompanyPerson', 'company_person', 'company_id', 'companyId');
    }


    /**
     * @param $pk
     * @return \NormTests\mysql\Company
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\mysql\Company
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\mysql\Company
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}