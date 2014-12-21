<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class AddressCollectionBase extends NormBaseCollection {
    public static $realm = 'mysql';
    public static $tableName = 'address';
    public static $singularClassName = '\NormTests\mysql\Address';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = 'id';
    public static $primaryDatastoreName = '__norm_test_mysql_ds';
}