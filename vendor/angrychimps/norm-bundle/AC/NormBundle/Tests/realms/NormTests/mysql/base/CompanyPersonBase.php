<?php
namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyPersonBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_mysql_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'mysql';

    /** @var  string */
    public static $tableName = 'company_person';

    /** @var string[] */
    public static $fieldNames = array('company_id', 'person_id', 'status');

    /** @var string[] */
    public static $fieldTypes = array('int', 'int', 'int');

    /** @var  string[] */
    public static $propertyNames = array('companyId', 'personId', 'status');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('company', 'person');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('company', 'person');

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

    const EMPLOYED_STATUS = 1;
    const TERMINATED_STATUS = 2;
    const QUIT_STATUS = 3;


    /** @var int */
    public $companyId;

    /** @var int */
    public $personId;

    /** @var int */
    public $status;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\mysql\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \NormTests\mysql\Person */
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
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\mysql\CompanyPerson
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}