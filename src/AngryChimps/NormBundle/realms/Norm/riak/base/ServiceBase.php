<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ServiceBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'service';

    /** @var string[] */
    protected static $fieldNames = array('id', 'company_id', 'name', 'body', 'discounted_price', 'original_price', 'mins_for_service', 'mins_notice', 'category', 'status', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'float', 'float', 'int', 'int', 'int', 'int', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'companyId', 'name', 'body', 'discountedPrice', 'originalPrice', 'minsForService', 'minsNotice', 'category', 'status', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = 'id';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ENABLED_STATUS = 1;
    const DISABLED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var string */
    public $companyId;

    /** @var string */
    public $name;

    /** @var string */
    public $body;

    /** @var float */
    public $discountedPrice;

    /** @var float */
    public $originalPrice;

    /** @var int */
    public $minsForService;

    /** @var int */
    public $minsNotice;

    /** @var int */
    public $category;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Company */
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
     * @return \Norm\riak\Service
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Service
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Service
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}