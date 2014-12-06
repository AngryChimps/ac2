<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MemberCompanyRatingBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'member_company_rating';

    /** @var string[] */
    protected static $fieldNames = array('member_id', 'company_id', 'rating', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('memberId', 'companyId', 'rating', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('member_id', 'company_id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('memberId', 'companyId');

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
    public $memberId;

    /** @var string */
    public $companyId;

    /** @var int */
    public $rating;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \Norm\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \Norm\riak\Member */
    public function getMember() {
        if($this->Member === null) {
            $this->loadMember();
        }
        return $this->Member;
    }


    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }

    protected function loadMember() {
        parent::loadProperty('Member', 'member', 'id');
    }




    /**
     * @param $pk
     * @return \Norm\riak\MemberCompanyRating
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\MemberCompanyRating
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\MemberCompanyRating
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}