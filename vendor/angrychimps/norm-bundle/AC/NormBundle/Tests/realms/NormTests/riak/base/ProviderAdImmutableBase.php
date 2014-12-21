<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdImmutableBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'provider_ad_immutable';

    /** @var string[] */
    public static $fieldNames = array('id', 'provider_ad', 'location', 'company', 'calendar', 'services', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', '\Norm\riak\ProviderAd', '\Norm\riak\Location', '\Norm\riak\Company', '\Norm\riak\Calendar', '\Norm\riak\ServiceCollection', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'providerAd', 'location', 'company', 'calendar', 'services', 'createdAt');

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


    /** @var string */
    public $id;

    /** @var \Norm\riak\ProviderAd */
    public $providerAd;

    /** @var \Norm\riak\Location */
    public $location;

    /** @var \Norm\riak\Company */
    public $company;

    /** @var \Norm\riak\Calendar */
    public $calendar;

    /** @var \Norm\riak\ServiceCollection */
    public $services;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

        $this->services = new \Norm\riak\ServiceCollection();
    }



    /** @return NormTests\riak\ProviderAd */
    public function getProviderAdCollection() {
        if($this->ProviderAd === null) {
            $this->loadProviderAd();
        }
        return $this->ProviderAd;
    }


    protected function loadProviderAdCollection() {
        parent::loadPropertyCollection('ProviderAd', 'provider_ad', 'current_immutable_id', 'currentImmutableId');
    }


    /**
     * @param $pk
     * @return \NormTests\riak\ProviderAdImmutable
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\ProviderAdImmutable
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\ProviderAdImmutable
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}