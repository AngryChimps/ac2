<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class BookingBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'booking';

    /** @var string[] */
    protected static $fieldNames = array('id', 'title', 'booking_detail_id', 'type', 'start', 'end');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'title', 'bookingDetailId', 'type', 'start', 'end');

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

    /** @var DateTime */
    public $start;

    /** @var DateTime */
    public $end;


    /** @returns Norm\riak\BookingDetail */
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
     * @return \Norm\riak\Booking
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Booking
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Booking
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}