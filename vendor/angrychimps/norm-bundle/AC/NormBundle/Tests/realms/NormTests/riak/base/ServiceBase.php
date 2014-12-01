<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ServiceBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'service';

    /** @var string[] */
    protected static $fieldNames = array('id', 'mysql_id', 'name', 'description', 'discounted_price', 'original_price', 'mins_for_service', 'mins_notice', 'category_id', 'status', 'updated_at', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'float', 'float', 'int', 'int', 'int', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'name', 'description', 'discountedPrice', 'originalPrice', 'minsForService', 'minsNotice', 'categoryId', 'status', 'updatedAt', 'createdAt');

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

    /** @var int */
    public $mysqlId;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var float */
    public $discountedPrice;

    /** @var float */
    public $originalPrice;

    /** @var int */
    public $minsForService;

    /** @var int */
    public $minsNotice;

    /** @var int */
    public $categoryId;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $updatedAt;

    /** @var DateTime */
    public $createdAt;




    /** @returns NormTests\riak\Ad */
    public function getAdCollection() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @returns NormTests\riak\AdFlag */
    public function getAdFlagCollection() {
        if($this->AdFlag === null) {
            $this->loadAdFlag();
        }
        return $this->AdFlag;
    }

    /** @returns NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @returns NormTests\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }


    protected function loadAdCollection() {
        parent::loadPropertyCollection('Ad', 'ad', 'service_id', 'serviceId');
    }

    protected function loadAdFlagCollection() {
        parent::loadPropertyCollection('AdFlag', 'ad_flag', 'service_id', 'serviceId');
    }

    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'service_id', 'serviceId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'service_id', 'serviceId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\Service
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Service
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Service
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}