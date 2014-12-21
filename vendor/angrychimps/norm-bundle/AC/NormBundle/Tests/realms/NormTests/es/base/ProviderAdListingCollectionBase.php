<?php

namespace NormTests\es\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdListingCollectionBase extends NormBaseCollection {
    public static $realm = 'es';
    public static $tableName = 'provider_ad_listing';
    public static $singularClassName = '\NormTests\es\ProviderAdListing';
    public static $primaryKeyFieldNames = array('provider_ad_id');
    public static $primaryKeyPropertyNames = array('providerAdId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_es_ds';
}