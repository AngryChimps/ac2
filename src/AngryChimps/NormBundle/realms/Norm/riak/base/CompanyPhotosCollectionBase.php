<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyPhotosCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'company_photos';
    protected static $singularClassName = 'CompanyPhotos';
    protected static $primaryKeyFieldNames = array('company_id');
    protected static $primaryKeyPropertyNames = array('companyId');
    protected static $autoIncrementFieldName = '';
}