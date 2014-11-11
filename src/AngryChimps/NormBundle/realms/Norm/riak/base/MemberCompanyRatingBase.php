<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class MemberCompanyRatingBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'member_company_rating';

    /** @var string[] */
    protected static $fieldNames = array('rating');

    /** @var string[] */
    protected static $fieldTypes = array('int');

    /** @var  string[] */
    protected static $propertyNames = array('rating');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array();

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array();

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


    /** @var int */
    public $rating;






    /**
     * @param $pk
     * @return MemberCompanyRating
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return MemberCompanyRating
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return MemberCompanyRating
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}