<?php

namespace Norm\mysql\base;

use norm\core\NormBaseCollection;

class MemberCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'member';
    protected static $singularClassName = 'Member';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}