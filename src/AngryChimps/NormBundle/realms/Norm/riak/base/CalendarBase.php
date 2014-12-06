<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CalendarBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'calendar';

    /** @var string[] */
    protected static $fieldNames = array('id', 'location_id', 'company_id', 'name', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'locationId', 'companyId', 'name', 'status', 'createdAt', 'updatedAt');

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
    const DISABED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var string */
    public $locationId;

    /** @var string */
    public $companyId;

    /** @var string */
    public $name;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \Norm\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @return \Norm\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }


    protected function loadLocation() {
        parent::loadProperty('Location', 'location', 'id');
    }

    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }


    /** @return Norm\riak\CalendarDay */
    public function getCalendarDayCollection() {
        if($this->CalendarDay === null) {
            $this->loadCalendarDay();
        }
        return $this->CalendarDay;
    }

    /** @return Norm\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
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


    protected function loadCalendarDayCollection() {
        parent::loadPropertyCollection('CalendarDay', 'calendar_day', 'calendar_id', 'calendarId');
    }

    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'calendar_id', 'calendarId');
    }

    protected function loadProviderAdListingCollection() {
        parent::loadPropertyCollection('ProviderAdListing', 'provider_ad_listing', 'calendar_id', 'calendarId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'calendar_id', 'calendarId');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Calendar
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Calendar
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Calendar
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}