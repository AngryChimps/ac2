<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MessageBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'message';

    /** @var string[] */
    public static $fieldNames = array('id', 'ad_id', 'author_key', 'body', 'status', 'flags', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'int', 'MessageFlag[]', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'adId', 'authorKey', 'body', 'status', 'flags', 'createdAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

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

    /** @return \NormTests\riak\ProviderAd */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @return \NormTests\riak\Member */
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


    /** @return NormTests\riak\MessageFlag */
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
     * @return \NormTests\riak\Message
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Message
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Message
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}