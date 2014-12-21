<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdFlagCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'provider_ad_flag';
    public static $singularClassName = '\NormTests\riak\ProviderAdFlag';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}