<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class BookingDetailBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'booking_detail';

    /** @var string[] */
    protected static $fieldNames = array('id', 'location_id', 'company_id', 'calendar_id', 'service_id', 'ad_id', 'member_id', 'start', 'end', 'payment_type', 'payment_id', 'cancellation_policy', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'DateTime', 'DateTime', 'int', 'string', 'string', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'locationId', 'companyId', 'calendarId', 'serviceId', 'adId', 'memberId', 'start', 'end', 'paymentType', 'paymentId', 'cancellationPolicy', 'status', 'createdAt', 'updatedAt');

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

    /** @var DateTime */
    public $start;

    /** @var DateTime */
    public $end;

    /** @var int */
    public $paymentType;

    /** @var string */
    public $paymentId;

    /** @var string */
    public $cancellationPolicy;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;


    /** @returns NormTests\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @returns NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @returns NormTests\riak\Calendar */
    public function getCalendar() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }

    /** @returns NormTests\riak\Ad */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @returns NormTests\riak\Service */
    public function getService() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
    }

    /** @returns NormTests\riak\Member */
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
        parent::loadProperty('Ad', 'ad', 'id');
    }

    protected function loadService() {
        parent::loadProperty('Service', 'service', 'id');
    }

    protected function loadMember() {
        parent::loadProperty('Member', 'member', 'id');
    }


    /** @returns NormTests\riak\Booking */
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
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\BookingDetail
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}