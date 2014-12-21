<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdFlagBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'provider_ad_flag';

    /** @var string[] */
    public static $fieldNames = array('id', 'author_id', 'company_id', 'location_id', 'ad_id', 'service_id', 'body', 'status', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'authorId', 'companyId', 'locationId', 'adId', 'serviceId', 'body', 'status', 'createdAt');

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

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\riak\Member */
    public function getAuthor() {
        if($this->Author === null) {
            $this->loadAuthor();
        }
        return $this->Author;
    }

    /** @return \NormTests\riak\Company */
    public function getCompany() {
        if($this->Company === null) {
            $this->loadCompany();
        }
        return $this->Company;
    }

    /** @return \NormTests\riak\Location */
    public function getLocation() {
        if($this->Location === null) {
            $this->loadLocation();
        }
        return $this->Location;
    }

    /** @return \NormTests\riak\ProviderAd */
    public function getAd() {
        if($this->Ad === null) {
            $this->loadAd();
        }
        return $this->Ad;
    }

    /** @return \NormTests\riak\Service */
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
        parent::loadProperty('Ad', 'provider_ad', 'id');
    }

    protected function loadService() {
        parent::loadProperty('Service', 'service', 'id');
    }




    /**
     * @param $pk
     * @return \NormTests\riak\ProviderAdFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\ProviderAdFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\ProviderAdFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}