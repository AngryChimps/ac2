<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ReviewBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'review';

    /** @var string[] */
    protected static $fieldNames = array('id', 'author_id', 'company_id', 'location_id', 'ad_id', 'service_id', 'author_screenname', 'body', 'rating', 'flags', 'status', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', '\Norm\riak\ReviewFlagCollection', 'int', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'authorId', 'companyId', 'locationId', 'adId', 'serviceId', 'authorScreenname', 'body', 'rating', 'flags', 'status', 'createdAt');

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

    const ENABLED_STATUS = 1;
    const DISABLED_STATUS = 2;


    /** @var string */
    public $id;

    /** @var string */
    public $authorId;

    /** @var string */
    public $companyId;

    /** @var string */
    public $locationId;

    /** @var string */
    public $adId;

    /** @var string */
    public $serviceId;

    /** @var string */
    public $authorScreenname;

    /** @var string */
    public $body;

    /** @var int */
    public $rating;

    /** @var \Norm\riak\ReviewFlagCollection */
    public $flags;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }

    /** @returns Norm\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @returns Norm\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @returns Norm\riak\Ad */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @returns Norm\riak\Service */
    public function getService() {
        if($this->Service === null) {
            $this->loadService();
        }
        return $this->Service;
    }


    protected function loadAuthor() {
        parent::loadProperty('Author', 'member', 'id');
    }

    protected function loadCompany() {
        parent::loadProperty('Company', 'company', 'id');
    }

    protected function loadLocation() {
        parent::loadProperty('Location', 'location', 'id');
    }

    protected function loadAd() {
        parent::loadProperty('Ad', 'ad', 'id');
    }

    protected function loadService() {
        parent::loadProperty('Service', 'service', 'id');
    }




    /**
     * @param $pk
     * @return \Norm\riak\Review
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Review
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Review
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}