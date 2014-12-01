<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CalendarDayBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'calendar_day';

    /** @var string[] */
    protected static $fieldNames = array('calendar_id', 'date', 'availabilities', 'bookings', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'Date', '\Norm\riak\AvailabilityCollection', '\Norm\riak\BookingCollection', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('calendarId', 'date', 'availabilities', 'bookings', 'status', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('calendar_id', 'date');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('calendarId', 'date');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = '';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ENABLED_STATUS = 1;
    const DISABED_STATUS = 2;


    /** @var string */
    public $calendarId;

    /** @var Date */
    public $date;

    /** @var \Norm\riak\AvailabilityCollection */
    public $availabilities;

    /** @var \Norm\riak\BookingCollection */
    public $bookings;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;


    /** @returns Norm\riak\Calendar */
    public function getCalendar() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }


    protected function loadCalendar() {
        parent::loadProperty('Calendar', 'calendar', 'id');
    }




    /**
     * @param $pk
     * @return \Norm\riak\CalendarDay
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\CalendarDay
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\CalendarDay
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}