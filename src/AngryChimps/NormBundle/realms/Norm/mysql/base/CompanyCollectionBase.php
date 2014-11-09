<?php

namespace Norm\mysql\base;

use norm\core\NormBaseCollection;

class CompanyCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'company';
    protected static $singularClassName = 'Company';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}