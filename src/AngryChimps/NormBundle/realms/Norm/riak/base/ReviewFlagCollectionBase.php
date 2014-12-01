<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ReviewFlagCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'review_flag';
    protected static $singularClassName = 'ReviewFlag';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}