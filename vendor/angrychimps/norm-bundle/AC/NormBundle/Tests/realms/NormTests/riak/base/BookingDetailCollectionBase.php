<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class BookingDetailCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'booking_detail';
    protected static $singularClassName = 'BookingDetail';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}