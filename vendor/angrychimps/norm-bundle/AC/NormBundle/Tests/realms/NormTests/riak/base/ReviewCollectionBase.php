<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ReviewCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'review';
    public static $singularClassName = '\NormTests\riak\Review';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}