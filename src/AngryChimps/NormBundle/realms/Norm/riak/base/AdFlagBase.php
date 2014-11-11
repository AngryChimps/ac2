<?php
namespace Norm\riak\base;

use AC\NormBundle\core\NormBaseObject;

class AdFlagBase extends NormBaseObject {

    /** @var  string */
    protected static $primaryDatastoreName = '';

    /** @var  string */
    protected static $cacheDatastoreName = '';

    /** @var  string */
    protected static $realm = 'riak';

    /** @var  string */
    protected static $tableName = 'ad_flag';

    /** @var string[] */
    protected static $fieldNames = array('ad_key', 'author_key', 'body', 'status', 'created_at');

    /** @var string[] */
    protected static $fieldTypes = array('string', 'string', 'string', 'int', 'DateTime');

    /** @var  string[] */
    protected static $propertyNames = array('adKey', 'authorKey', 'body', 'status', 'createdAt');

    /** @var  string[] */
    protected static $primaryKeyFieldNames = array('ad_key', 'author_key');

    /** @var  string[] */
    protected static $primaryKeyPropertyNames = array('adKey', 'authorKey');

    /** @var  string[] */
    protected static $autoIncrementFieldName = '';

    /** @var  string[] */
    protected static $autoIncrementPropertyName = '';

    /** @var bool */
    protected static $hasPrimaryKey = true;

    /** @var bool */
    protected static $hasAutoIncrement = false;


    /** @var string */
    public $adKey;

    /** @var string */
    public $authorKey;

    /** @var string */
    public $body;

    /** @var int */
    public $status;

    /** @var DateTime */
    public $createdAt;


    /** @returns Norm\riak\Ad */
    public function getAd_() {
        if($this->Ad_ === null) {
            $this->loadAd_();
        }
        return $this->Ad_;
    }

    /** @returns Norm\riak\Member */
    public function getAuthor_() {
        if($this->Author_ === null) {
            $this->loadAuthor_();
        }
        return $this->Author_;
    }


    protected function loadAd_() {
        parent::loadProperty('Ad_', 'ad', 'key');
    }

    protected function loadAuthor_() {
        parent::loadProperty('Author_', 'member', 'key');
    }




    /**
     * @param $pk
     * @return AdFlag
     */
    public static function getByPk($pk) {
        return parent::getByPk($pk);
    }

    /**
     * @param $where string The WHERE clause (excluding the word WHERE)
     * @param array $params The parameter count
     * @return AdFlag
     */
    public static function getByWhere($where, $params = array()) {
        return parent::getByWhere($where, $params);
    }

    /**
     * @param $sql The complete sql statement with placeholders
     * @param array $params The parameter array to replace placeholders in the sql
     * @return AdFlag
     */
    public static function getBySql($sql, $params = array()) {
        return parent::getBySql($sql, $params);
    }

}