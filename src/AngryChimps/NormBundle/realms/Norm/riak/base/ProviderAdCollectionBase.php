<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class ProviderAdCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'provider_ad';
    protected static $singularClassName = 'ProviderAd';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}