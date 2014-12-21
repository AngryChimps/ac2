<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdImmutableCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'provider_ad_immutable';
    public static $singularClassName = '\NormTests\riak\ProviderAdImmutable';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}