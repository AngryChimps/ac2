<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdListingBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'provider_ad_listing';

    /** @var string[] */
    protected static $fieldNames = array('ad_id', 'location_id', 'company_id', 'calendar_id', 'author_id', 'category_id', 'title', 'description', 'ad_flag_total', 'photos', 'service_ids', 'flag_ids', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'int', 'string', 'string', 'int', 'string[]', 'string[]', 'string[]', 'int', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('adId', 'locationId', 'companyId', 'calendarId', 'authorId', 'categoryId', 'title', 'description', 'adFlagTotal', 'photos', 'serviceIds', 'flagIds', 'status', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('ad_id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('adId');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var  string[] */
    protected static $autoGenerateFieldName = 'ad_id';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = 'adId';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ACTIVE_STATUS = 1;
    const CLOSED_STATUS = 2;
    const PROHIBITED_STATUS = 3;


    /** @var string */
    public $adId;

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

    /** @return \Norm\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @return \Norm\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \Norm\riak\Calendar */
    public function getCalendar() {
        if($this->Calendar === null) {
            $this->loadCalendar();
        }
        return $this->Calendar;
    }

    /** @return \Norm\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }

    /** @return \Norm\riak\Service */
    public function getService() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
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




    /**
     * @param $pk
     * @return \Norm\riak\ProviderAdListing
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\ProviderAdListing
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\ProviderAdListing
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}