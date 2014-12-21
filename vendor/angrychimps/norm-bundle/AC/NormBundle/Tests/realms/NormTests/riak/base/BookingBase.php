<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class BookingBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'booking';

    /** @var string[] */
    public static $fieldNames = array('id', 'title', 'booking_detail_id', 'type', 'start', 'end');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'title', 'bookingDetailId', 'type', 'start', 'end');

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

    const SHORT_BOOKING_TYPE = 1;
    const SYSTEM_BOOKING_TYPE = 2;


    /** @var string */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $bookingDetailId;

    /** @var int */
    public $type;

    /** @var \DateTime */
    public $start;

    /** @var \DateTime */
    public $end;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\riak\BookingDetail */
    public function getBooking_detail() {
        if($this->Booking_detail === null) {
            $this->loadBooking_detail();
        }
        return $this->Booking_detail;
    }


    protected function loadBooking_detail() {
        parent::loadProperty('Booking_detail', 'booking_detail', 'id');
    }




    /**
     * @param $pk
     * @return \NormTests\riak\Booking
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Booking
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Booking
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}