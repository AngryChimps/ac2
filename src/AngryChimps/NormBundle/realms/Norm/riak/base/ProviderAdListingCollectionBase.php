<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdListingCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'provider_ad_listing';
    protected static $singularClassName = 'ProviderAdListing';
    protected static $primaryKeyFieldNames = array('ad_id');
    protected static $primaryKeyPropertyNames = array('adId');
    protected static $autoIncrementFieldName = '';
}