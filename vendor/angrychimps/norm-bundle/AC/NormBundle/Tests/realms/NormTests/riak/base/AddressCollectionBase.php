<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AddressCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'address';
    public static $singularClassName = '\NormTests\riak\Address';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}