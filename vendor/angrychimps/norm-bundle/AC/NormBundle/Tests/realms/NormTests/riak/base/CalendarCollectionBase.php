<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CalendarCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'calendar';
    protected static $singularClassName = 'Calendar';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}