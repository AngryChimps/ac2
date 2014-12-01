<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AdFlagCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'ad_flag';
    protected static $singularClassName = 'AdFlag';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}