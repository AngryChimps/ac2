<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class AdCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'ad';
    protected static $singularClassName = 'Ad';
    protected static $primaryKeyFieldNames = array('ad_key');
    protected static $primaryKeyPropertyNames = array('adKey');
    protected static $autoIncrementFieldName = '';
}