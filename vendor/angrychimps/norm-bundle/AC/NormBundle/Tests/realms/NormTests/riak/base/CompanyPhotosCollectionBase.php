<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyPhotosCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'company_photos';
    public static $singularClassName = '\NormTests\riak\CompanyPhotos';
    public static $primaryKeyFieldNames = array('company_id');
    public static $primaryKeyPropertyNames = array('companyId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}