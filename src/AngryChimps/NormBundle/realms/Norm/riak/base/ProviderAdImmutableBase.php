<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ProviderAdImmutableBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'provider_ad_immutable';

    /** @var string[] */
    protected static $fieldNames = array('id', 'provider_ad', 'location', 'company', 'calendar', 'services', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', '\Norm\riak\ProviderAd', '\Norm\riak\Location', '\Norm\riak\Company', '\Norm\riak\Calendar', '\Norm\riak\ServiceCollection', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'providerAd', 'location', 'company', 'calendar', 'services', 'createdAt');

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





    /**
     * @param $pk
     * @return \Norm\riak\ProviderAdImmutable
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\ProviderAdImmutable
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\ProviderAdImmutable
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}