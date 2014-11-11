<?php

namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseCollection;

class CommentCollectionBase extends NormBaseCollection {
    protected static $realm = 'riak';
    protected static $tableName = 'comment';
    protected static $singularClassName = 'Comment';
    protected static $primaryKeyFieldNames = array('comment_key');
    protected static $primaryKeyPropertyNames = array('commentKey');
    protected static $autoIncrementFieldName = '';
}