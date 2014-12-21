<?php

namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class MessageFlagCollectionBase extends NormBaseCollection {
    public static $realm = 'riak';
    public static $tableName = 'message_flag';
    public static $singularClassName = '\NormTests\riak\MessageFlag';
    public static $primaryKeyFieldNames = array('message_key', 'author_key');
    public static $primaryKeyPropertyNames = array('messageKey', 'authorKey');
    public static $autoIncrementFieldName = '';
    public static $primaryDatastoreName = '__norm_test_riak_ds';
}