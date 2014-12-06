<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyReviewsBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'company_reviews';

    /** @var string[] */
    protected static $fieldNames = array('company_id', 'review_ids', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string[]', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('companyId', 'reviewIds', 'updatedAt');

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
    public $reviewIds;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->reviewIds = array();
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
     * @return \Norm\riak\CompanyReviews
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\CompanyReviews
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\CompanyReviews
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}