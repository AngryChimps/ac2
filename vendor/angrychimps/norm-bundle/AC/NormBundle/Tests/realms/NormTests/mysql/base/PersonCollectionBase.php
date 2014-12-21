<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class PersonCollectionBase extends NormBaseCollection {
    public static $realm = 'mysql';
    public static $tableName = 'person';
    public static $singularClassName = '\NormTests\mysql\Person';
    public static $primaryKeyFieldNames = array('id');
    public static $primaryKeyPropertyNames = array('id');
    public static $autoIncrementFieldName = 'id';
    public static $primaryDatastoreName = '__norm_test_mysql_ds';
}