<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdImmutableCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'provider_ad_immutable';
    protected static $singularClassName = 'ProviderAdImmutable';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}