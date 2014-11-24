<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'member';
    protected static $singularClassName = 'Member';
    protected static $primaryKeyFieldNames = array('id');
    protected static $primaryKeyPropertyNames = array('id');
    protected static $autoIncrementFieldName = '';
}