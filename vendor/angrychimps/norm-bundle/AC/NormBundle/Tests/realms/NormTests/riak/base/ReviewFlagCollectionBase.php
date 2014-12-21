<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ReviewFlagCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'review_flag';
    public static $singularClassName = '\NormTests\riak\ReviewFlag';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}