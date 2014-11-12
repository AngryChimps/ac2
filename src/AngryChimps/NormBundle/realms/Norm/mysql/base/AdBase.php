<?php
namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class AdBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'ad';

    /** @var string[] */
    protected static $fieldNames = array('id', 'key', 'location_key', 'company_key', 'category_id', 'subcategory_id', 'title', 'description', 'minutes_required', 'minutes_booking_notice', 'price', 'discount', 'status', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'string', 'int', 'int', 'string', 'string', 'int', 'int', 'decimal', 'float', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'key', 'locationKey', 'companyKey', 'categoryId', 'subcategoryId', 'title', 'description', 'minutesRequired', 'minutesBookingNotice', 'price', 'discount', 'status', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('id');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;

    const ACTIVE_STATUS = 1;
    const CLOSED_STATUS = 2;
    const PROHIBITED_STATUS = 3;


    /** @var int */
    public $id;

    /** @var string */
    public $key;

    /** @var string */
    public $locationKey;

    /** @var string */
    public $companyKey;

    /** @var int */
    public $categoryId;

    /** @var int */
    public $subcategoryId;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var int */
    public $minutesRequired;

    /** @var int */
    public $minutesBookingNotice;

    /** @var decimal */
    public $price;

    /** @var float */
    public $discount;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;






    /**
     * @param $pk
     * @return Ad
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Ad
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Ad
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}