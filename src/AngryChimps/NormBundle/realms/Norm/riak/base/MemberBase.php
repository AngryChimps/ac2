<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MemberBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'member';

    /** @var string[] */
    protected static $fieldNames = array('id', 'mysql_id', 'email', 'password', 'name', 'fb_id', 'fb_access_token', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'administer_member_ids', 'blocked_company_ids', 'managed_company_ids', 'ad_flag_keys', 'message_flag_keys', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', 'Date', 'string', 'int', 'int', 'string[]', 'string[]', 'string[]', 'string[]', 'string[]', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'email', 'password', 'name', 'fbId', 'fbAccessToken', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'administerMemberIds', 'blockedCompanyIds', 'managedCompanyIds', 'adFlagKeys', 'messageFlagKeys', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = 'id';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ACTIVE_STATUS = 1;
    const DELETED_STATUS = 2;
    const LOCKED_STATUS = 3;
    const BANNED_STATUS = 4;

    const USER_ROLE = 1;
    const SUPPORT_ROLE = 2;
    const ADMIN_ROLE = 3;
    const SUPER_ADMIN_ROLE = 4;


    /** @var string */
    public $id;

    /** @var int */
    public $mysqlId;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $name;

    /** @var string */
    public $fbId;

    /** @var string */
    public $fbAccessToken;

    /** @var string */
    public $fname;

    /** @var string */
    public $lname;

    /** @var string */
    public $gender;

    /** @var string */
    public $locale;

    /** @var int */
    public $timezone;

    /** @var Date */
    public $dob;

    /** @var string */
    public $photo;

    /** @var int */
    public $status;

    /** @var int */
    public $role;

    /** @var string[] */
    public $administerMemberIds;

    /** @var string[] */
    public $blockedCompanyIds;

    /** @var string[] */
    public $managedCompanyIds;

    /** @var string[] */
    public $adFlagKeys;

    /** @var string[] */
    public $messageFlagKeys;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;




    /** @returns Norm\riak\AdFlag */
    public function getAdFlagCollection() {
        if($this->AdFlag === null) {
            $this->loadAdFlag();
        }
        return $this->AdFlag;
    }

    /** @returns Norm\riak\Comment */
    public function getCommentCollection() {
        if($this->Comment === null) {
            $this->loadComment();
        }
        return $this->Comment;
    }

    /** @returns Norm\riak\Message */
    public function getMessageCollection() {
        if($this->Message === null) {
            $this->loadMessage();
        }
        return $this->Message;
    }

    /** @returns Norm\riak\MessageFlag */
    public function getMessageFlagCollection() {
        if($this->MessageFlag === null) {
            $this->loadMessageFlag();
        }
        return $this->MessageFlag;
    }


    protected function loadAdFlagCollection() {
        parent::loadPropertyCollection('AdFlag', 'ad_flag', 'author_key', 'authorKey');
    }

    protected function loadCommentCollection() {
        parent::loadPropertyCollection('Comment', 'comment', 'member_key', 'memberKey');
    }

    protected function loadMessageCollection() {
        parent::loadPropertyCollection('Message', 'message', 'author_key', 'authorKey');
    }

    protected function loadMessageFlagCollection() {
        parent::loadPropertyCollection('MessageFlag', 'message_flag', 'author_key', 'authorKey');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Member
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Member
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Member
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}