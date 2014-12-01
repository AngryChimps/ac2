<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CompanyBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'company';

    /** @var string[] */
    protected static $fieldNames = array('id', 'mysql_id', 'name', 'description', 'plan', 'rating_count', 'rating_total', 'rating_avg', 'administer_member_ids', 'location_ids', 'flag_ids', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'int', 'string', 'string', 'int', 'int', 'int', 'float', 'string[]', 'string[]', 'string[]', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'mysqlId', 'name', 'description', 'plan', 'ratingCount', 'ratingTotal', 'ratingAvg', 'administerMemberIds', 'locationIds', 'flagIds', 'status', 'createdAt', 'updatedAt');

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

    const BASIC_PLAN = 1;
    const PREMIUM_PLAN = 2;

    const ENABLED_STATUS = 1;
    const DISABLED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var int */
    public $mysqlId;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var int */
    public $plan;

    /** @var int */
    public $ratingCount;

    /** @var int */
    public $ratingTotal;

    /** @var float */
    public $ratingAvg;

    /** @var string[] */
    public $administerMemberIds;

    /** @var string[] */
    public $locationIds;

    /** @var string[] */
    public $flagIds;

    /** @var int */
    public $status;

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

    /** @returns Norm\riak\Location */
    public function getLocationCollection() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @returns Norm\riak\MemberCompanyRating */
    public function getMemberCompanyRatingCollection() {
        if($this->MemberCompanyRating === null) {
            $this->loadMemberCompanyRating();
        }
        return $this->MemberCompanyRating;
    }

    /** @returns Norm\riak\Calendar */
    public function getCalendarCollection() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }

    /** @returns Norm\riak\CompanyServices */
    public function getCompanyServicesCollection() {
        if($this->CompanyServices === null) {
            $this->loadCompanyServices();
        }
        return $this->CompanyServices;
    }

    /** @returns Norm\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @returns Norm\riak\CompanyReviews */
    public function getCompanyReviewsCollection() {
        if($this->CompanyReviews === null) {
            $this->loadCompanyReviews();
        }
        return $this->CompanyReviews;
    }

    /** @returns Norm\riak\Review */
    public function getReviewCollection() {
        if($this->Review === null) {
            $this->loadReview();
        }
        return $this->Review;
    }


    protected function loadAdCollection() {
        parent::loadPropertyCollection('Ad', 'ad', 'company_id', 'companyId');
    }

    protected function loadAdFlagCollection() {
        parent::loadPropertyCollection('AdFlag', 'ad_flag', 'company_id', 'companyId');
    }

    protected function loadLocationCollection() {
        parent::loadPropertyCollection('Location', 'location', 'company_id', 'companyId');
    }

    protected function loadMemberCompanyRatingCollection() {
        parent::loadPropertyCollection('MemberCompanyRating', 'member_company_rating', 'company_id', 'companyId');
    }

    protected function loadCalendarCollection() {
        parent::loadPropertyCollection('Calendar', 'calendar', 'company_id', 'companyId');
    }

    protected function loadCompanyServicesCollection() {
        parent::loadPropertyCollection('CompanyServices', 'company_services', 'company_id', 'companyId');
    }

    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'company_id', 'companyId');
    }

    protected function loadCompanyReviewsCollection() {
        parent::loadPropertyCollection('CompanyReviews', 'company_reviews', 'company_id', 'companyId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'company_id', 'companyId');
    }


    /**
     * @param $pk
     * @return \Norm\riak\Company
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Company
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Company
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}