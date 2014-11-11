<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MessageFlagBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'message_flag';

    /** @var string[] */
    protected static $fieldNames = array('message_key', 'author_key', 'body', 'status', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'int', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('messageKey', 'authorKey', 'body', 'status', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('message_key', 'author_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('messageKey', 'authorKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


    /** @var string */
    public $messageKey;

    /** @var string */
    public $authorKey;

    /** @var string */
    public $body;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Message */
    public function getMessage_() {
        if($this->Message_ === null) {
            $this->loadMessage_();
        }
        return $this->Message_;
    }

    /** @returns Norm\riak\Member */
    public function getAuthor_() {
        if($this->Author_ === null) {
            $this->loadAuthor_();
        }
        return $this->Author_;
    }


    protected function loadMessage_() {
        parent::loadProperty('Message_', 'message', 'key');
    }

    protected function loadAuthor_() {
        parent::loadProperty('Author_', 'member', 'key');
    }




    /**
     * @param $pk
     * @return MessageFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return MessageFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return MessageFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}