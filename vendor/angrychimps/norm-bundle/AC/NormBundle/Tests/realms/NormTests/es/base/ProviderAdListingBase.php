<?php
namespace NormTests\es\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdListingBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_es_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'es';

    /** @var  string */
    public static $tableName = 'provider_ad_listing';

    /** @var string[] */
    public static $fieldNames = array('provider_ad_id', 'current_immutable_id', 'company_name', 'title', 'photo', 'address', 'rating', 'availabilities', 'updated_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'string', 'string', '\Norm\riak\Address', 'float', '\Norm\riak\AvailabilityCollection', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('providerAdId', 'currentImmutableId', 'companyName', 'title', 'photo', 'address', 'rating', 'availabilities', 'updatedAt');

    /** @var  string[] */
    public static $primaryKeyFieldNames = array('provider_ad_id');

    /** @var  string[] */
    public static $primaryKeyPropertyNames = array('providerAdId');

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


    /** @var string */
    public $providerAdId;

    /** @var string */
    public $currentImmutableId;

    /** @var string */
    public $companyName;

    /** @var string */
    public $title;

    /** @var string */
    public $photo;

    /** @var \Norm\riak\Address */
    public $address;

    /** @var float */
    public $rating;

    /** @var \Norm\riak\AvailabilityCollection */
    public $availabilities;

    /** @var \DateTime */
    public $updatedAt;


    public function __construct() {
        parent::__construct();

        $this->availabilities = new \Norm\riak\AvailabilityCollection();
    }





    /**
     * @param $pk
     * @return \NormTests\es\ProviderAdListing
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\es\ProviderAdListing
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\es\ProviderAdListing
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}