<?php

namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class LocationCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'location';
    protected static $singularClassName = 'Location';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}