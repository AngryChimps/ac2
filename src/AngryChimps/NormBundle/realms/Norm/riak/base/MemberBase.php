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
    protected static $fieldNames = array('id', 'mysql_id', 'email', 'password', 'name', 'fb_id', 'fb_access_token', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'blocked_company_ids', 'managed_company_ids', 'ad_flag_keys', 'message_flag_keys', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', 'Date', 'string', 'int', 'int', 'string[]', 'string[]', 'string[]', 'string[]', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'email', 'password', 'name', 'fbId', 'fbAccessToken', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'blockedCompanyIds', 'managedCompanyIds', 'adFlagKeys', 'messageFlagKeys', 'createdAt', 'updatedAt');

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




    /** @returns Norm\riak\Ad */
    public function getAdCollection() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @returns Norm\riak\AdFlag */
    public function getAdFlagCollection() {
        if($this->AdFlag === null) {
            $this->loadAdFlag();
        }
        return $this->AdFlag;
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

    /** @returns Norm\riak\MemberCompanyRating */
    public function getMemberCompanyRatingCollection() {
        if($this->MemberCompanyRating === null) {
            $this->loadMemberCompanyRating();
        }
        return $this->MemberCompanyRating;
    }

    /** @returns Norm\riak\Session */
    public function getSessionCollection() {
        if($this->Session === null) {
            $this->loadSession();
        }
        return $this->Session;
    }

    /** @returns Norm\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @returns Norm\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }

    /** @returns Norm\riak\ReviewFlag */
    public function getReviewFlagCollection() {
        if($this->ReviewFlag === null) {
            $this->loadReviewFlag();
        }
        return $this->ReviewFlag;
    }


    protected function loadAdCollection() {
        parent::loadPropertyCollection('Ad', 'ad', 'author_id', 'authorId');
    }

    protected function loadAdFlagCollection() {
        parent::loadPropertyCollection('AdFlag', 'ad_flag', 'author_id', 'authorId');
    }

    protected function loadMessageCollection() {
        parent::loadPropertyCollection('Message', 'message', 'author_id', 'authorId');
    }

    protected function loadMessageFlagCollection() {
        parent::loadPropertyCollection('MessageFlag', 'message_flag', 'author_key', 'authorKey');
    }

    protected function loadMemberCompanyRatingCollection() {
        parent::loadPropertyCollection('MemberCompanyRating', 'member_company_rating', 'member_id', 'memberId');
    }

    protected function loadSessionCollection() {
        parent::loadPropertyCollection('Session', 'session', 'user_id', 'userId');
    }

    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'member_id', 'memberId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'author_id', 'authorId');
    }

    protected function loadReviewFlagCollection() {
        parent::loadPropertyCollection('ReviewFlag', 'review_flag', 'author_id', 'authorId');
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