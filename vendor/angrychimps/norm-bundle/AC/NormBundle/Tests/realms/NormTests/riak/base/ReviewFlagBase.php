<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class ReviewFlagBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'review_flag';

    /** @var string[] */
    public static $fieldNames = array('id', 'author_id', 'body', 'status', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'int', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'authorId', 'body', 'status', 'createdAt');

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


    protected function loadAuthor() {
        parent::loadProperty('Author', 'member', 'id');
    }




    /**
     * @param $pk
     * @return \NormTests\riak\ReviewFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\ReviewFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\ReviewFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}