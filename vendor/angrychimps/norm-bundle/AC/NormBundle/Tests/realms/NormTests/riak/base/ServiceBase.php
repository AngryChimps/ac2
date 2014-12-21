<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ServiceBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'service';

    /** @var string[] */
    public static $fieldNames = array('id', 'mysql_id', 'name', 'description', 'discounted_price', 'original_price', 'mins_for_service', 'mins_notice', 'status', 'updated_at', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'int', 'string', 'string', 'float', 'float', 'int', 'int', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'mysqlId', 'name', 'description', 'discountedPrice', 'originalPrice', 'minsForService', 'minsNotice', 'status', 'updatedAt', 'createdAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    public static $autoIncrementFieldName = '';

    /** @var  string[] */
    public static $autoIncrementPropertyName = '';

    /** @var  string[] */
    public static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    public static $autoGeneratePropertyName = 'id';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = false;

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
    public $status;

    /** @var \DateTime */
    public $updatedAt;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }



    /** @return NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return NormTests\riak\ProviderAd */
    public function getProviderAdCollection() {
        if($this->ProviderAd === null) {
            $this->loadProviderAd();
        }
        return $this->ProviderAd;
    }

    /** @return NormTests\riak\ProviderAdFlag */
    public function getProviderAdFlagCollection() {
        if($this->ProviderAdFlag === null) {
            $this->loadProviderAdFlag();
        }
        return $this->ProviderAdFlag;
    }

    /** @return NormTests\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }


    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'service_id', 'serviceId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'service_id', 'serviceId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'service_id', 'serviceId');
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
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Service
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}