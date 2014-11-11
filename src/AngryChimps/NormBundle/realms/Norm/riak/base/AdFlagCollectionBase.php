<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AdFlagCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'ad_flag';
    protected static $singularClassName = 'AdFlag';
    protected static $primaryKeyFieldNames = array('ad_key', 'author_key');
    protected static $primaryKeyPropertyNames = array('adKey', 'authorKey');
    protected static $autoIncrementFieldName = '';
}