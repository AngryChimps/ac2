<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyCollectionBase extends NormBaseCollection {
    public static $realm = 'mysql';
    public static $tableName = 'company';
    public static $singularClassName = '\NormTests\mysql\Company';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = 'id';
    public static $primaryDatastoreName = '__norm_test_mysql_ds';
}