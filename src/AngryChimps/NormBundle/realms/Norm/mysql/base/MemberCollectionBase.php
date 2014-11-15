<?php

namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseCollection;

class MemberCollectionBase extends NormBaseCollection {
    protected static $realm = 'mysql';
    protected static $tableName = 'member';
    protected static $singularClassName = 'Member';
    protected static $primaryKeyFieldNames = array('mysql_id');
    protected static $primaryKeyPropertyNames = array('mysqlId');
    protected static $autoIncrementFieldName = '';
}