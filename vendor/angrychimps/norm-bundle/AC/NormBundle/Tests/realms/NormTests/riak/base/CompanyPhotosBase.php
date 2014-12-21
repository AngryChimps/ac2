<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyPhotosBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'company_photos';

    /** @var string[] */
    public static $fieldNames = array('company_id', 'photos');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string[]');

    /** @var  string[] */
    public static $propertyNames = array('companyId', 'photos');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('company_id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('companyId');

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


    /** @var string */
    public $companyId;

    /** @var string[] */
    public $photos;


    public function __construct() {
        parent::__construct();

        $this->photos = array();
    }

    /** @return \NormTests\riak\Company */
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
     * @return \NormTests\riak\CompanyPhotos
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\CompanyPhotos
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\CompanyPhotos
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}