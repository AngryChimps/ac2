<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'provider_ad';

    /** @var string[] */
    public static $fieldNames = array('id', 'location_id', 'company_id', 'calendar_id', 'author_id', 'category_id', 'current_immutable_id', 'title', 'description', 'ad_flag_total', 'photos', 'service_ids', 'flag_ids', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'int', 'string', 'string', 'string', 'int', 'string[]', 'string[]', 'string[]', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'locationId', 'companyId', 'calendarId', 'authorId', 'categoryId', 'currentImmutableId', 'title', 'description', 'adFlagTotal', 'photos', 'serviceIds', 'flagIds', 'status', 'createdAt', 'updatedAt');

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

    const PUBLISHED_STATUS = 1;
    const UN_PUBISHED_STATUS = 2;
    const DELETED_STATUS = 3;


    /** @var string */
    public $id;

    /** @var string */
    public $locationId;

    /** @var string */
    public $companyId;

    /** @var string */
    public $calendarId;

    /** @var string */
    public $authorId;

    /** @var int */
    public $categoryId;

    /** @var string */
    public $currentImmutableId;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var int */
    public $adFlagTotal;

    /** @var string[] */
    public $photos;

    /** @var string[] */
    public $serviceIds;

    /** @var string[] */
    public $flagIds;

    /** @var int */
    public $status;

    /** @var \DateTime */
    public $createdAt;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->photos = array();
        $this->serviceIds = array();
        $this->flagIds = array();
    }

    /** @return \NormTests\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @return \NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \NormTests\riak\Calendar */
    public function getCalendar() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }

    /** @return \NormTests\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }

    /** @return \NormTests\riak\Service */
    public function getService() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
    }

    /** @return \NormTests\riak\ProviderAdImmutable */
    public function getCurrent_immutable() {
        if($this->Current_immutable === null) {
            $this->loadCurrent_immutable();
        }
        return $this->Current_immutable;
    }


    protected function loadLocation() {
        parent::loadProperty('Location', 'location', 'id');
    }

    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }

    protected function loadCalendar() {
        parent::loadProperty('Calendar', 'calendar', 'id');
    }

    protected function loadAuthor() {
        parent::loadProperty('Author', 'member', 'id');
    }

    protected function loadService() {
        parent::loadProperty('Service', 'service', 'id');
    }

    protected function loadCurrent_immutable() {
        parent::loadProperty('Current_immutable', 'provider_ad_immutable', 'id');
    }


    /** @return NormTests\riak\BookingDetail */
    public function getBookingDetailCollection() {
        if($this->BookingDetail === null) {
            $this->loadBookingDetail();
        }
        return $this->BookingDetail;
    }

    /** @return NormTests\riak\Message */
    public function getMessageCollection() {
        if($this->Message === null) {
            $this->loadMessage();
        }
        return $this->Message;
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


    protected function loadBookingDetailCollection() {
        parent::loadPropertyCollection('BookingDetail', 'booking_detail', 'ad_id', 'adId');
    }

    protected function loadMessageCollection() {
        parent::loadPropertyCollection('Message', 'message', 'ad_id', 'adId');
    }

    protected function loadProviderAdFlagCollection() {
        parent::loadPropertyCollection('ProviderAdFlag', 'provider_ad_flag', 'ad_id', 'adId');
    }

    protected function loadReviewCollection() {
        parent::loadPropertyCollection('Review', 'review', 'ad_id', 'adId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\ProviderAd
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\ProviderAd
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\ProviderAd
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}