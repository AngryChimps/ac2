<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CalendarBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'calendar';

    /** @var string[] */
    public static $fieldNames = array('id', 'location_id', 'company_id', 'name', 'availabilities', 'bookings', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', '\Norm\riak\AvailabilityCollection', '\Norm\riak\BookingCollection', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'locationId', 'companyId', 'name', 'availabilities', 'bookings', 'status', 'createdAt', 'updatedAt');

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
    const DISABED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var string */
    public $locationId;

    /** @var string */
    public $companyId;

    /** @var string */
    public $name;

    /** @var \Norm\riak\AvailabilityCollection */
    public $availabilities;

    /** @var \Norm\riak\BookingCollection */
    public $bookings;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->availabilities = new \NormTests\riak\AvailabilityCollection();
        $this->bookings = new \NormTests\riak\BookingCollection();
    }

    /** @return \NormTests\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @return \NormTests\riak\Company */
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


    /** @return \NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return \NormTests\riak\ProviderAd */
    public function getProviderAdCollection() {
        if($this->ProviderAd === null) {
            $this->loadProviderAd();
        }
        return $this->ProviderAd;
    }


    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'calendar_id', 'calendarId');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'calendar_id', 'calendarId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\Calendar
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Calendar
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Calendar
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}