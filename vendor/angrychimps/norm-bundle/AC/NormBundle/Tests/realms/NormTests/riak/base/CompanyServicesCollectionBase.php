<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyServicesCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'company_services';
    public static $singularClassName = '\NormTests\riak\CompanyServices';
    public static $primaryKeyFieldNames = array('company_id');
    public static $primaryKeyPropertyNames = array('companyId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}