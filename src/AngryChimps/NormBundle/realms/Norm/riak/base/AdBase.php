<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class AdBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'ad';

    /** @var string[] */
    protected static $fieldNames = array('ad_key', 'location_key', 'company_key', 'category_name', 'subcategory_name', 'title', 'description', 'minutes_required', 'minutes_booking_notice', 'minutes_avail_interval', 'price', 'discount', 'body', 'flag_total', 'flag_keys', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', 'int', 'int', 'decimal', 'float', 'string', 'int', 'string[]', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('adKey', 'locationKey', 'companyKey', 'categoryName', 'subcategoryName', 'title', 'description', 'minutesRequired', 'minutesBookingNotice', 'minutesAvailInterval', 'price', 'discount', 'body', 'flagTotal', 'flagKeys', 'status', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('ad_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('adKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ActiveStatus = 1;
    const ClosedStatus = 2;
    const ProhibitedStatus = 3;

    /** @var string */
    public $adKey;

    /** @var string */
    public $locationKey;

    /** @var string */
    public $companyKey;

    /** @var string */
    public $categoryName;

    /** @var string */
    public $subcategoryName;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var int */
    public $minutesRequired;

    /** @var int */
    public $minutesBookingNotice;

    /** @var int */
    public $minutesAvailInterval;

    /** @var decimal */
    public $price;

    /** @var float */
    public $discount;

    /** @var string */
    public $body;

    /** @var int */
    public $flagTotal;

    /** @var string[] */
    public $flagKeys;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;




    /** @returns Norm\riak\AdFlag */
    public function getAdFlagCollection() {
        if($this->AdFlag === null) {
            $this->loadAdFlag();
        }
        return $this->AdFlag;
    }

    /** @returns Norm\riak\Message */
    public function getMessageCollection() {
        if($this->Message === null) {
            $this->loadMessage();
        }
        return $this->Message;
    }


    protected function loadAdFlagCollection() {
        parent::loadPropertyCollection('AdFlag', 'ad_flag', 'ad_key', 'adKey');
    }

    protected function loadMessageCollection() {
        parent::loadPropertyCollection('Message', 'message', 'ad_key', 'adKey');
    }


    /**
     * @param $pk
     * @return Ad
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Ad
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Ad
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}