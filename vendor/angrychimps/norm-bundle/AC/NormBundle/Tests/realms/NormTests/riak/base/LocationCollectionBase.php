<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class LocationCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'location';
    protected static $singularClassName = 'Location';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}