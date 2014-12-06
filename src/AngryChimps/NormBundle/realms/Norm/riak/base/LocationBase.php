<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class LocationBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'location';

    /** @var string[] */
    protected static $fieldNames = array('id', 'company_id', 'calendarIds', 'name', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'lat', 'long', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string[]', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'float', 'float', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'companyId', 'calendarIds', 'name', 'street1', 'street2', 'city', 'state', 'zip', 'phone', 'lat', 'long', 'status', 'createdAt', 'updatedAt');

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

    /** @var string[] */
    public $calendarIds;

    /** @var string */
    public $name;

    /** @var string */
    public $street1;

    /** @var string */
    public $street2;

    /** @var string */
    public $city;

    /** @var string */
    public $state;

    /** @var string */
    public $zip;

    /** @var string */
    public $phone;

    /** @var float */
    public $lat;

    /** @var float */
    public $long;

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

    /** @return \Norm\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }


    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }


    /** @return Norm\riak\Calendar */
    public function getCalendarCollection() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
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


    protected function loadCalendarCollection() {
        parent::loadPropertyCollection('Calendar', 'calendar', 'location_id', 'locationId');
    }

    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'location_id', 'locationId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'location_id', 'locationId');
    }

    protected function loadProviderAdListingCollection() {
        parent::loadPropertyCollection('ProviderAdListing', 'provider_ad_listing', 'location_id', 'locationId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'location_id', 'locationId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'location_id', 'locationId');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Location
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Location
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Location
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}