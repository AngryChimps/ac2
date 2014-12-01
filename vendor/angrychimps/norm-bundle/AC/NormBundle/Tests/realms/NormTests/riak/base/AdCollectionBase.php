<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AdCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'ad';
    protected static $singularClassName = 'Ad';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}