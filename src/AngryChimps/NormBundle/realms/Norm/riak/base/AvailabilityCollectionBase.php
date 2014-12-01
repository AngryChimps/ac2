<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AvailabilityCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'availability';
    protected static $singularClassName = 'Availability';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}