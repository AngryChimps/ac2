<?php
namespace Norm\mysql\base;

use AC\NormBundle\core\NormBaseObject;

class MemberBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'mysql';

    /** @var  string */
    protected static $tableName = 'member';

    /** @var string[] */
    protected static $fieldNames = array('id', 'key', 'email', 'password', 'name', 'fb_id', 'fb_auth_token', 'ac_token', 'fname', 'lname', 'dob', 'photo', 'status', 'role', 'blocked_company_keys', 'managed_company_keys', 'ad_flag_keys', 'message_flag_keys', 'created_at', 'updated_at');

    /** @var string[] */
    protected static $fieldTypes = array('int', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'string', 'Date', 'string', 'int', 'int', 'string[]', 'string[]', 'string[]', 'string[]', 'DateTime', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('id', 'key', 'email', 'password', 'name', 'fbId', 'fbAuthToken', 'acToken', 'fname', 'lname', 'dob', 'photo', 'status', 'role', 'blockedCompanyKeys', 'managedCompanyKeys', 'adFlagKeys', 'messageFlagKeys', 'createdAt', 'updatedAt');

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

    const ActiveStatus = 1;
    const DeletedStatus = 2;
    const LockedStatus = 3;
    const BannedStatus = 4;
    const UserRole = 1;
    const SupportRole = 2;
    const AdminRole = 3;
    const SuperAdminRole = 4;

    /** @var int */
    public $id;

    /** @var string */
    public $key;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var string */
    public $name;

    /** @var string */
    public $fbId;

    /** @var string */
    public $fbAuthToken;

    /** @var string */
    public $acToken;

    /** @var string */
    public $fname;

    /** @var string */
    public $lname;

    /** @var Date */
    public $dob;

    /** @var string */
    public $photo;

    /** @var int */
    public $status;

    /** @var int */
    public $role;

    /** @var string[] */
    public $blockedCompanyKeys;

    /** @var string[] */
    public $managedCompanyKeys;

    /** @var string[] */
    public $adFlagKeys;

    /** @var string[] */
    public $messageFlagKeys;

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