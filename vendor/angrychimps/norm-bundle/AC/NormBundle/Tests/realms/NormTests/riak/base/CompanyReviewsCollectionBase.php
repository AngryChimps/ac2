<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyReviewsCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'company_reviews';
    public static $singularClassName = '\NormTests\riak\CompanyReviews';
    public static $primaryKeyFieldNames = array('company_id');
    public static $primaryKeyPropertyNames = array('companyId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}