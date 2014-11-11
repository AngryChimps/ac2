<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class CommentBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'comment';

    /** @var string[] */
    protected static $fieldNames = array('comment_key', 'company_key', 'author_key', 'rating', 'body', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'int', 'string', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('commentKey', 'companyKey', 'authorKey', 'rating', 'body', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('comment_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('commentKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


    /** @var string */
    public $commentKey;

    /** @var string */
    public $companyKey;

    /** @var string */
    public $authorKey;

    /** @var int */
    public $rating;

    /** @var string */
    public $body;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Company */
    public function getCompany_() {
        if($this->Company_ === null) {
            $this->loadCompany_();
        }
        return $this->Company_;
    }

    /** @returns Norm\riak\Member */
    public function getMember_() {
        if($this->Member_ === null) {
            $this->loadMember_();
        }
        return $this->Member_;
    }


    protected function loadCompany_() {
        parent::loadProperty('Company_', 'company', 'key');
    }

    protected function loadMember_() {
        parent::loadProperty('Member_', 'member', 'key');
    }




    /**
     * @param $pk
     * @return Comment
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return Comment
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return Comment
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}