<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class CustomerCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'customer';
    protected static $singularClassName = 'Customer';
    protected static $primaryKeyFieldNames = array('person_id');
    protected static $primaryKeyPropertyNames = array('personId');
    protected static $autoIncrementFieldName = '';
}