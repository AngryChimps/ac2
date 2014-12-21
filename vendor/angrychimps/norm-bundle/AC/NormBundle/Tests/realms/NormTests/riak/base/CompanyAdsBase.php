<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyAdsBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'company_ads';

    /** @var string[] */
    public static $fieldNames = array('company_id', 'published_ad_ids', 'unpublished_ad_ids', 'deleted_ad_ids');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string[]', 'string[]', 'string[]');

    /** @var  string[] */
    public static $propertyNames = array('companyId', 'publishedAdIds', 'unpublishedAdIds', 'deletedAdIds');

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
    public $publishedAdIds;

    /** @var string[] */
    public $unpublishedAdIds;

    /** @var string[] */
    public $deletedAdIds;


    public function __construct() {
        parent::__construct();

        $this->publishedAdIds = array();
        $this->unpublishedAdIds = array();
        $this->deletedAdIds = array();
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
     * @return \NormTests\riak\CompanyAds
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\CompanyAds
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\CompanyAds
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}