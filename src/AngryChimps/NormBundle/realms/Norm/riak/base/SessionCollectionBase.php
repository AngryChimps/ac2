<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class SessionCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'session';
    protected static $singularClassName = 'Session';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}