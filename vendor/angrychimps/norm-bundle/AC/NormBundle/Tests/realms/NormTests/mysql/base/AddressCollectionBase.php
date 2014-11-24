<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class AddressCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'address';
    protected static $singularClassName = 'Address';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = 'id';
}