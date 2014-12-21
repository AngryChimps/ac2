<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class LocationBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'location';

    /** @var string[] */
    public static $fieldNames = array('id', 'company_id', 'calendarIds', 'name', 'address', 'phone', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string[]', 'string', '\Norm\riak\Address', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'companyId', 'calendarIds', 'name', 'address', 'phone', 'status', 'createdAt', 'updatedAt');

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

    /** @var string */
    public $companyId;

    /** @var string[] */
    public $calendarIds;

    /** @var string */
    public $name;

    /** @var \Norm\riak\Address */
    public $address;

    /** @var string */
    public $phone;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->calendarIds = array();
    }

    /** @return \NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }


    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }


    /** @return NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return NormTests\riak\Calendar */
    public function getCalendarCollection() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
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
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'location_id', 'locationId');
    }

    protected function loadCalendarCollection() {
        parent::loadPropertyCollection('Calendar', 'calendar', 'location_id', 'locationId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'location_id', 'locationId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'location_id', 'locationId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'location_id', 'locationId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\Location
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Location
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Location
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}