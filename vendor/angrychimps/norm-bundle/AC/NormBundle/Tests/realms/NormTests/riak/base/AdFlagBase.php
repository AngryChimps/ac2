<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class AdFlagBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'ad_flag';

    /** @var string[] */
    protected static $fieldNames = array('id', 'author_id', 'company_id', 'location_id', 'ad_id', 'service_id', 'body', 'status', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'authorId', 'companyId', 'locationId', 'adId', 'serviceId', 'body', 'status', 'createdAt');

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
    public $body;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;


    /** @returns NormTests\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }

    /** @returns NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @returns NormTests\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @returns NormTests\riak\Ad */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @returns NormTests\riak\Service */
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
     * @return \NormTests\riak\AdFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\AdFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\AdFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}