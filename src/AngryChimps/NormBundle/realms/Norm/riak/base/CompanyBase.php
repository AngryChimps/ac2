<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'company';

    /** @var string[] */
    protected static $fieldNames = array('id', 'mysql_id', 'name', 'description', 'address', 'plan', 'rating_count', 'rating_total', 'rating_avg', 'flag_total', 'administer_member_ids', 'location_keys', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'string', 'int', 'int', 'int', 'float', 'int', 'string[]', 'string[]', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'name', 'description', 'address', 'plan', 'ratingCount', 'ratingTotal', 'ratingAvg', 'flagTotal', 'administerMemberIds', 'locationKeys', 'status', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

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

    const BASIC_PLAN = 1;
    const PREMIUM_PLAN = 2;

    const ENABLED_STATUS = 1;
    const DISABLED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var int */
    public $mysqlId;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var string */
    public $address;

    /** @var int */
    public $plan;

    /** @var int */
    public $ratingCount;

    /** @var int */
    public $ratingTotal;

    /** @var float */
    public $ratingAvg;

    /** @var int */
    public $flagTotal;

    /** @var string[] */
    public $administerMemberIds;

    /** @var string[] */
    public $locationKeys;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;




    /** @returns Norm\riak\Comment */
    public function getCommentCollection() {
        if($this->Comment === null) {
            $this->loadComment();
        }
        return $this->Comment;
    }

    /** @returns Norm\riak\Location */
    public function getLocationCollection() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @returns Norm\riak\Service */
    public function getServiceCollection() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
    }


    protected function loadCommentCollection() {
        parent::loadPropertyCollection('Comment', 'comment', 'company_key', 'companyKey');
    }

    protected function loadLocationCollection() {
        parent::loadPropertyCollection('Location', 'location', 'company_id', 'companyId');
    }

    protected function loadServiceCollection() {
        parent::loadPropertyCollection('Service', 'service', 'company_id', 'companyId');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Company
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Company
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Company
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}