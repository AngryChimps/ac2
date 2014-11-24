<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'company';

    /** @var string[] */
    protected static $fieldNames = array('id', 'name', 'address_id');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'int');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'name', 'addressId');

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
    public $name;

    /** @var int */
    public $addressId;


    /** @returns NormTests\mysql\Address */
    public function getAddress() {
        if($this->Address === null) {
            $this->loadAddress();
        }
        return $this->Address;
    }


    protected function loadAddress() {
        parent::loadProperty('Address', 'address', 'id');
    }


    /** @returns NormTests\mysql\CompanyPerson */
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
     * @return Company
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Company
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Company
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}