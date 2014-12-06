<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MessageCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'message';
    protected static $singularClassName = 'Message';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}