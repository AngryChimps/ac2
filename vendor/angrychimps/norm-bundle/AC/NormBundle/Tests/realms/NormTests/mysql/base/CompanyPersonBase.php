<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyPersonBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'company_person';

    /** @var string[] */
    protected static $fieldNames = array('company_id', 'person_id', 'status');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'int', 'int');

    /** @var  string[] */
    protected static $propertyNames = array('companyId', 'personId', 'status');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('company', 'person');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('company', 'person');

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

    const EMPLOYED_STATUS = 1;
    const TERMINATED_STATUS = 2;
    const QUIT_STATUS = 3;


    /** @var int */
    public $companyId;

    /** @var int */
    public $personId;

    /** @var int */
    public $status;


    /** @returns NormTests\mysql\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @returns NormTests\mysql\Person */
    public function getPerson() {
        if($this->Person === null) {
            $this->loadPerson();
        }
        return $this->Person;
    }


    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }

    protected function loadPerson() {
        parent::loadProperty('Person', 'person', 'id');
    }




    /**
     * @param $pk
     * @return \NormTests\mysql\CompanyPerson
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\mysql\CompanyPerson
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\mysql\CompanyPerson
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}