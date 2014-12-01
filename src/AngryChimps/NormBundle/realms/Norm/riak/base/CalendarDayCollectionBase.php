<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CalendarDayCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'calendar_day';
    protected static $singularClassName = 'CalendarDay';
    protected static $primaryKeyFieldNames = array('calendar_id', 'date');
    protected static $primaryKeyPropertyNames = array('calendarId', 'date');
    protected static $autoIncrementFieldName = '';
}