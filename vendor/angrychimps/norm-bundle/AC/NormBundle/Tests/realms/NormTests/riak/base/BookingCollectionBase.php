<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class BookingCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'booking';
    protected static $singularClassName = 'Booking';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}