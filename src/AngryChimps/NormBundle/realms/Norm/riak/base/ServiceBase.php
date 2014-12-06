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
    protected static $fieldNames = array('id', 'mysql_id', 'name', 'description', 'discounted_price', 'original_price', 'mins_for_service', 'mins_notice', 'status', 'updated_at', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'float', 'float', 'int', 'int', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'name', 'description', 'discountedPrice', 'originalPrice', 'minsForService', 'minsNotice', 'status', 'updatedAt', 'createdAt');

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
    public $status;

    /** @var \DateTime */
    public $updatedAt;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }



    /** @return Norm\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return Norm\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }

    /** @return Norm\riak\ProviderAdListing */
    public function getProviderAdListingCollection() {
        if($this->ProviderAdListing === null) {
            $this->loadProviderAdListing();
        }
        return $this->ProviderAdListing;
    }

    /** @return Norm\riak\ProviderAd */
    public function getProviderAdCollection() {
        if($this->ProviderAd === null) {
            $this->loadProviderAd();
        }
        return $this->ProviderAd;
    }

    /** @return Norm\riak\ProviderAdFlag */
    public function getProviderAdFlagCollection() {
        if($this->ProviderAdFlag === null) {
            $this->loadProviderAdFlag();
        }
        return $this->ProviderAdFlag;
    }


    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'service_id', 'serviceId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'service_id', 'serviceId');
    }

    protected function loadProviderAdListingCollection() {
        parent::loadPropertyCollection('ProviderAdListing', 'provider_ad_listing', 'service_id', 'serviceId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'service_id', 'serviceId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'service_id', 'serviceId');
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
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Service
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}