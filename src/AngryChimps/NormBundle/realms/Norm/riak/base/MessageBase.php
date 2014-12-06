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
    protected static $fieldNames = array('id', 'ad_id', 'author_key', 'body', 'status', 'flags', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'int', 'MessageFlag[]', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'adId', 'authorKey', 'body', 'status', 'flags', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

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
    public $id;

    /** @var string */
    public $adId;

    /** @var string */
    public $authorKey;

    /** @var string */
    public $body;

    /** @var int */
    public $status;

    /** @var MessageFlag[] */
    public $flags;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \Norm\riak\ProviderAd */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @return \Norm\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }


    protected function loadAd() {
        parent::loadProperty('Ad', 'provider_ad', 'id');
    }

    protected function loadAuthor() {
        parent::loadProperty('Author', 'member', 'id');
    }


    /** @return Norm\riak\MessageFlag */
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
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Message
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}