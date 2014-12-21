<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class BookingDetailBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'booking_detail';

    /** @var string[] */
    public static $fieldNames = array('id', 'location_id', 'company_id', 'calendar_id', 'service_id', 'ad_id', 'member_id', 'start', 'end', 'payment_type', 'payment_id', 'cancellation_policy', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', '\DateTime', '\DateTime', 'int', 'string', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'locationId', 'companyId', 'calendarId', 'serviceId', 'adId', 'memberId', 'start', 'end', 'paymentType', 'paymentId', 'cancellationPolicy', 'status', 'createdAt', 'updatedAt');

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

    const PAY_PAL_PAYMENT__TYPE = 1;
    const CREDIT_CARD_PAYMENT__TYPE = 2;

    const PENDING_STATUS = 1;
    const COMPLETED_STATUS = 2;
    const CANCELED_STATUS = 3;


    /** @var string */
    public $id;

    /** @var string */
    public $locationId;

    /** @var string */
    public $companyId;

    /** @var string */
    public $calendarId;

    /** @var string */
    public $serviceId;

    /** @var string */
    public $adId;

    /** @var string */
    public $memberId;

    /** @var \DateTime */
    public $start;

    /** @var \DateTime */
    public $end;

    /** @var int */
    public $paymentType;

    /** @var string */
    public $paymentId;

    /** @var string */
    public $cancellationPolicy;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

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

    /** @return \NormTests\riak\Calendar */
    public function getCalendar() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }

    /** @return \NormTests\riak\ProviderAd */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @return \NormTests\riak\Service */
    public function getService() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
    }

    /** @return \NormTests\riak\Member */
    public function getMember() {
        if($this->Member === null) {
            $this->loadMember();
        }
        return $this->Member;
    }


    protected function loadLocation() {
        parent::loadProperty('Location', 'location', 'id');
    }

    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }

    protected function loadCalendar() {
        parent::loadProperty('Calendar', 'calendar', 'id');
    }

    protected function loadAd() {
        parent::loadProperty('Ad', 'provider_ad', 'id');
    }

    protected function loadService() {
        parent::loadProperty('Service', 'service', 'id');
    }

    protected function loadMember() {
        parent::loadProperty('Member', 'member', 'id');
    }


    /** @return NormTests\riak\Booking */
    public function getBookingCollection() {
        if($this->Booking === null) {
            $this->loadBooking();
        }
        return $this->Booking;
    }


    protected function loadBookingCollection() {
        parent::loadPropertyCollection('Booking', 'booking', 'booking_detail_id', 'bookingDetailId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\BookingDetail
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\BookingDetail
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\BookingDetail
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}