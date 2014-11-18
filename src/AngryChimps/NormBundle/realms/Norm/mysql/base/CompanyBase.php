<?php
namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'company';

    /** @var string[] */
    protected static $fieldNames = array('id', 'key', 'name', 'description', 'address', 'plan', 'rating_count', 'rating_total', 'rating_avg', 'flag_total', 'administer_member_ids', 'location_keys', '', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'string', 'string', 'int', 'int', 'int', 'float', 'int', 'string[]', 'string[]', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'key', 'name', 'description', 'address', 'plan', 'ratingCount', 'ratingTotal', 'ratingAvg', 'flagTotal', 'administerMemberIds', 'locationKeys', '', 'createdAt', 'updatedAt');

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

    const ACTIVE_ = 1;
    const CLOSED_ = 2;
    const PROHIBITED_ = 3;


    /** @var int */
    public $id;

    /** @var string */
    public $key;

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
    public $;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;




    /** @returns Norm\mysql\Location */
    public function getLocationCollection() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }


    protected function loadLocationCollection() {
        parent::loadPropertyCollection('Location', 'location', 'company_id', 'companyId');
    }


    /**
     * @param $pk
     * @return \Norm\mysql\Company
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\mysql\Company
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\mysql\Company
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}