<?php

namespace NormTests\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class PersonCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'person';
    protected static $singularClassName = 'Person';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = 'id';
}