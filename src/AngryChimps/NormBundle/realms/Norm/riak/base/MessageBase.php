<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MessageBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'message';

    /** @var string[] */
    protected static $fieldNames = array('message_key', 'ad_key', 'author_key', 'body', 'status', 'flags', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'int', 'MessageFlag[]', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('messageKey', 'adKey', 'authorKey', 'body', 'status', 'flags', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('message_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('messageKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = '';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const UNREAD_STATUS = 1;
    const READ_STATUS = 2;
    const DELETED_STATUS = 3;


    /** @var string */
    public $messageKey;

    /** @var string */
    public $adKey;

    /** @var string */
    public $authorKey;

    /** @var string */
    public $body;

    /** @var int */
    public $status;

    /** @var MessageFlag[] */
    public $flags;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Ad */
    public function getAd_() {
        if($this->Ad_ === null) {
            $this->loadAd_();
        }
        return $this->Ad_;
    }

    /** @returns Norm\riak\Member */
    public function getAuthor_() {
        if($this->Author_ === null) {
            $this->loadAuthor_();
        }
        return $this->Author_;
    }


    protected function loadAd_() {
        parent::loadProperty('Ad_', 'ad', 'key');
    }

    protected function loadAuthor_() {
        parent::loadProperty('Author_', 'member', 'key');
    }


    /** @returns Norm\riak\MessageFlag */
    public function getMessageFlagCollection() {
        if($this->MessageFlag === null) {
            $this->loadMessageFlag();
        }
        return $this->MessageFlag;
    }


    protected function loadMessageFlagCollection() {
        parent::loadPropertyCollection('MessageFlag', 'message_flag', 'message_key', 'messageKey');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Message
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Message
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Message
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}