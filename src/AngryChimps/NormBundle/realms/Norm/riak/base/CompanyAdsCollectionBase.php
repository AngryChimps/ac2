<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyAdsCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'company_ads';
    protected static $singularClassName = 'CompanyAds';
    protected static $primaryKeyFieldNames = array('company_id');
    protected static $primaryKeyPropertyNames = array('companyId');
    protected static $autoIncrementFieldName = '';
}