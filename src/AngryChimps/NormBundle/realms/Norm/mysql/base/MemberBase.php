<?php
namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class MemberBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = 'mysql_ds';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'member';

    /** @var string[] */
    protected static $fieldNames = array('mysql_id', 'id', 'email', 'password', 'name', 'fb_id', 'fb_access_token', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'int', 'Date', 'string', 'int', 'int', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('mysqlId', 'id', 'email', 'password', 'name', 'fbId', 'fbAccessToken', 'fname', 'lname', 'gender', 'locale', 'timezone', 'dob', 'photo', 'status', 'role', 'createdAt', 'updatedAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('mysql_id');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('mysqlId');

    /** @var  string[] */
    protected static $autoIncrementFieldName = 'mysql_id';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = 'mysqlId';

    /** @var  string[] */
    protected static $autoGenerateFieldName = 'id';

    /** @var  string[] */
    protected static $autoGeneratePropertyName = 'id';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = true;

    const ACTIVE_STATUS = 1;
    const DELETED_STATUS = 2;
    const LOCKED_STATUS = 3;
    const BANNED_STATUS = 4;

    const USER_ROLE = 1;
    const SUPPORT_ROLE = 2;
    const ADMIN_ROLE = 3;
    const SUPER_ADMIN_ROLE = 4;


    /** @var int */
    public $mysqlId;

    /** @var string */
    public $id;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $name;

    /** @var string */
    public $fbId;

    /** @var string */
    public $fbAccessToken;

    /** @var string */
    public $fname;

    /** @var string */
    public $lname;

    /** @var string */
    public $gender;

    /** @var string */
    public $locale;

    /** @var int */
    public $timezone;

    /** @var Date */
    public $dob;

    /** @var string */
    public $photo;

    /** @var int */
    public $status;

    /** @var int */
    public $role;

    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $updatedAt;






    /**
     * @param $pk
     * @return Member
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Member
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Member
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}