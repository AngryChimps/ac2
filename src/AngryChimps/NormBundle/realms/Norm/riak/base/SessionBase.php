<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class SessionBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'riak_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'session';

    /** @var string[] */
    protected static $fieldNames = array('id', 'user_id', 'browser_hash', 'session_bag', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'array', '\DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'userId', 'browserHash', 'sessionBag', 'createdAt');

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

    /** @return \Norm\riak\Member */
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
     * @return \Norm\riak\Session
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return \Norm\riak\Session
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql string The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return \Norm\riak\Session
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}