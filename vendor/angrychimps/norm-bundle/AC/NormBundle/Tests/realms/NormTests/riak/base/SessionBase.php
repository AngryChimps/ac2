<?php
namespace NormTests\riak\base;

use AC\NormBundle\core\NormBaseObject;

class SessionBase extends NormBaseObject {

    /** @var  string */
    public static $primaryDatastoreName = '__norm_test_riak_ds';

    /** @var  string */
    public static $cacheDatastoreName = '';

    /** @var  string */
    public static $realm = 'riak';

    /** @var  string */
    public static $tableName = 'session';

    /** @var string[] */
    public static $fieldNames = array('id', 'user_id', 'browser_hash', 'session_bag', 'created_at');

    /** @var string[] */
    public static $fieldTypes = array('string', 'string', 'string', 'array', '\DateTime');

    /** @var  string[] */
    public static $propertyNames = array('id', 'userId', 'browserHash', 'sessionBag', 'createdAt');

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

    /** @var string */
    public $userId;

    /** @var string */
    public $browserHash;

    /** @var array */
    public $sessionBag;

    /** @var \DateTime */
    public $createdAt;


    public function __construct() {
        parent::__construct();

    }

    /** @return \NormTests\riak\Member */
    public function getUser() {
        if($this->User === null) {
            $this->loadUser();
        }
        return $this->User;
    }


    protected function loadUser() {
        parent::loadProperty('User', 'member', 'id');
    }




    /**
     * @param $pk
     * @return \NormTests\riak\Session
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \NormTests\riak\Session
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \NormTests\riak\Session
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}