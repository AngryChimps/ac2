<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyAdsCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'company_ads';
    public static $singularClassName = '\NormTests\riak\CompanyAds';
    public static $primaryKeyFieldNames = array('company_id');
    public static $primaryKeyPropertyNames = array('companyId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}