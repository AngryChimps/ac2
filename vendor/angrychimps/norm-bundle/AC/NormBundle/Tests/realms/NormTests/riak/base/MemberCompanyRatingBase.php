<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MemberCompanyRatingBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'member_company_rating';

    /** @var string[] */
    public static $fieldNames = array('member_id', 'company_id', 'rating', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('memberId', 'companyId', 'rating', 'createdAt', 'updatedAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('member_id', 'company_id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('memberId', 'companyId');

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

    /** @return \NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \NormTests\riak\Member */
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
     * @return \NormTests\riak\MemberCompanyRating
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\MemberCompanyRating
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\MemberCompanyRating
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}