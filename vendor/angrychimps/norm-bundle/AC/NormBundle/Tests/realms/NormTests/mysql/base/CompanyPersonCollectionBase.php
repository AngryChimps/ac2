<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class CompanyPersonCollectionBase extends NormBaseCollection {
    public static $realm = 'mysql';
    public static $tableName = 'company_person';
    public static $singularClassName = '\NormTests\mysql\CompanyPerson';
    public static $primaryKeyFieldNames = array('company', 'person');
    public static $primaryKeyPropertyNames = array('company', 'person');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_mysql_ds';
}