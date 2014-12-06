<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdFlagCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'provider_ad_flag';
    protected static $singularClassName = 'ProviderAdFlag';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}