<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class LocationCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'location';
    protected static $singularClassName = 'Location';
    protected static $primaryKeyFieldNames = array('location_key');
    protected static $primaryKeyPropertyNames = array('locationKey');
    protected static $autoIncrementFieldName = '';
}