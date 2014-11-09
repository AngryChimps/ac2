<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'member';
    protected static $singularClassName = 'Member';
    protected static $primaryKeyFieldNames = array('member_key');
    protected static $primaryKeyPropertyNames = array('memberKey');
    protected static $autoIncrementFieldName = '';
}