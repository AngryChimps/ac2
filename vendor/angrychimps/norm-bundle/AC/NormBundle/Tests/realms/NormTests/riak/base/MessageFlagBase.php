<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MessageFlagBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'message_flag';

    /** @var string[] */
    public static $fieldNames = array('message_key', 'author_key', 'body', 'status', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'int', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('messageKey', 'authorKey', 'body', 'status', 'createdAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('message_key', 'author_key');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('messageKey', 'authorKey');

    /** @var  string[] */
    public static $autoIncrementFieldName = '';

    /** @var  string[] */
    public static $autoIncrementPropertyName = '';

    /** @var  string[] */
    public static $autoGenerateFieldName = '';

    /** @var  string[] */
    public static $autoGeneratePropertyName = '';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = false;


    /** @var string */
    public $messageKey;

    /** @var string */
    public $authorKey;

    /** @var string */
    public $body;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\riak\Message */
    public function getMessage_() {
        if($this->Message_ === null) {
            $this->loadMessage_();
        }
        return $this->Message_;
    }

    /** @return \NormTests\riak\Member */
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
     * @return \NormTests\riak\MessageFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\MessageFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\MessageFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}