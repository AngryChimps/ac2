<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyReviewsCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'company_reviews';
    protected static $singularClassName = 'CompanyReviews';
    protected static $primaryKeyFieldNames = array('company_id');
    protected static $primaryKeyPropertyNames = array('companyId');
    protected static $autoIncrementFieldName = '';
}