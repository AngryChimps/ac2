<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MemberBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'member';

    /** @var string[] */
    public static $fieldNames = array('id', 'mysql_id', 'email', 'password', 'name', 'mobile', 'fb_id', 'fb_access_token', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'blocked_company_ids', 'managed_company_ids', 'ad_flag_keys', 'message_flag_keys', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', '\DateTime', 'string', 'int', 'int', 'string[]', 'string[]', 'string[]', 'string[]', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'mysqlId', 'email', 'password', 'name', 'mobile', 'fbId', 'fbAccessToken', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'blockedCompanyIds', 'managedCompanyIds', 'adFlagKeys', 'messageFlagKeys', 'createdAt', 'updatedAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    public static $autoIncrementFieldName = '';

    /** @var  string[] */
    public static $autoIncrementPropertyName = '';

    /** @var  string[] */
    public static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    public static $autoGeneratePropertyName = 'id';

    /** @var bool */
    public static $hasPrimaryKey = true;

    /** @var bool */
    public static $hasAutoIncrement = false;

    const ACTIVE_STATUS = 1;
    const DELETED_STATUS = 2;
    const LOCKED_STATUS = 3;
    const BANNED_STATUS = 4;
    const PARTIAL_REGISTRATION_STATUS = 5;

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
    public $mobile;

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

    /** @var \DateTime */
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

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->blockedCompanyIds = array();
        $this->managedCompanyIds = array();
        $this->adFlagKeys = array();
        $this->messageFlagKeys = array();
    }



    /** @return NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return NormTests\riak\MemberCompanyRating */
    public function getMemberCompanyRatingCollection() {
        if($this->MemberCompanyRating === null) {
            $this->loadMemberCompanyRating();
        }
        return $this->MemberCompanyRating;
    }

    /** @return NormTests\riak\Message */
    public function getMessageCollection() {
        if($this->Message === null) {
            $this->loadMessage();
        }
        return $this->Message;
    }

    /** @return NormTests\riak\MessageFlag */
    public function getMessageFlagCollection() {
        if($this->MessageFlag === null) {
            $this->loadMessageFlag();
        }
        return $this->MessageFlag;
    }

    /** @return NormTests\riak\ProviderAd */
    public function getProviderAdCollection() {
        if($this->ProviderAd === null) {
            $this->loadProviderAd();
        }
        return $this->ProviderAd;
    }

    /** @return NormTests\riak\ProviderAdFlag */
    public function getProviderAdFlagCollection() {
        if($this->ProviderAdFlag === null) {
            $this->loadProviderAdFlag();
        }
        return $this->ProviderAdFlag;
    }

    /** @return NormTests\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }

    /** @return NormTests\riak\ReviewFlag */
    public function getReviewFlagCollection() {
        if($this->ReviewFlag === null) {
            $this->loadReviewFlag();
        }
        return $this->ReviewFlag;
    }

    /** @return NormTests\riak\Session */
    public function getSessionCollection() {
        if($this->Session === null) {
            $this->loadSession();
        }
        return $this->Session;
    }


    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'member_id', 'memberId');
    }

    protected function loadMemberCompanyRatingCollection() {
        parent::loadPropertyCollection('MemberCompanyRating', 'member_company_rating', 'member_id', 'memberId');
    }

    protected function loadMessageCollection() {
        parent::loadPropertyCollection('Message', 'message', 'author_id', 'authorId');
    }

    protected function loadMessageFlagCollection() {
        parent::loadPropertyCollection('MessageFlag', 'message_flag', 'author_key', 'authorKey');
    }

    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'author_id', 'authorId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'author_id', 'authorId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'author_id', 'authorId');
    }

    protected function loadReviewFlagCollection() {
        parent::loadPropertyCollection('ReviewFlag', 'review_flag', 'author_id', 'authorId');
    }

    protected function loadSessionCollection() {
        parent::loadPropertyCollection('Session', 'session', 'user_id', 'userId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\Member
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Member
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Member
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}