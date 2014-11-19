<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ServiceCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'service';
    protected static $singularClassName = 'Service';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}