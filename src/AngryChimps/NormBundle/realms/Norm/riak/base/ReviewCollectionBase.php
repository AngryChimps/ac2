<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ReviewCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'review';
    protected static $singularClassName = 'Review';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}