<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ZipcodeCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'zipcode';
    protected static $singularClassName = 'Zipcode';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}