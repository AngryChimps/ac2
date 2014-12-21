<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class CustomerCollectionBase extends NormBaseCollection {
    public static $realm = 'mysql';
    public static $tableName = 'customer';
    public static $singularClassName = '\NormTests\mysql\Customer';
    public static $primaryKeyFieldNames = array('person_id');
    public static $primaryKeyPropertyNames = array('personId');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_mysql_ds';
}