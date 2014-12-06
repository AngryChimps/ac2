<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyAdsBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'company_ads';

    /** @var string[] */
    protected static $fieldNames = array('company_id', 'published_ad_ids', 'unpublished_ad_ids', 'deleted_ad_ids');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string[]', 'string[]', 'string[]');

    /** @var  string[] */
    protected static $propertyNames = array('companyId', 'publishedAdIds', 'unpublishedAdIds', 'deletedAdIds');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('company_id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('companyId');

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

    /** @return \Norm\riak\Company */
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
     * @return \Norm\riak\CompanyAds
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\CompanyAds
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\CompanyAds
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}