<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'company';
    protected static $singularClassName = 'Company';
    protected static $primaryKeyFieldNames = array('company_key');
    protected static $primaryKeyPropertyNames = array('companyKey');
    protected static $autoIncrementFieldName = '';
}