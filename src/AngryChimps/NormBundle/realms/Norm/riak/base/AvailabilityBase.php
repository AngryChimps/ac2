<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class AvailabilityBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'availability';

    /** @var string[] */
    protected static $fieldNames = array('id', 'start', 'end');

    /** @var string[] */
    protected static $fieldTypes = array('string', '\DateTime', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'start', 'end');

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

    /** @var \DateTime */
    public $start;

    /** @var \DateTime */
    public $end;


    public function __construct() {
        parent::__construct();

    }





    /**
     * @param $pk
     * @return \Norm\riak\Availability
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Availability
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Availability
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}