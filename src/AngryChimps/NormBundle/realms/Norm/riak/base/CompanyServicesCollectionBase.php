<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyServicesCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'company_services';
    protected static $singularClassName = 'CompanyServices';
    protected static $primaryKeyFieldNames = array('company_id');
    protected static $primaryKeyPropertyNames = array('companyId');
    protected static $autoIncrementFieldName = '';
}