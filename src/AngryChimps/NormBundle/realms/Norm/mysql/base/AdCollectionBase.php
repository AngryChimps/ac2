<?php

namespace Norm\mysql\base;

use norm\core\NormBaseCollection;

class AdCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'ad';
    protected static $singularClassName = 'Ad';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}