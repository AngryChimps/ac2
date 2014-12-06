<?php


namespace AC\NormBundle\core\datastore;


abstract class AbstractRiakDatastore extends AbstractDatastore {
    const PREFIX = '__norm';

    /** @var \Riak\Connection  */
    public $connection;

    /** @var \Riak\Bucket[] */
    protected $buckets = array();

    public function __construct($configParams) {
        $this->connection = new \Riak\Connection($configParams['host'], $configParams['port']);
    }

    protected function _getBucketName($realm, $tablename) {
        return self::PREFIX . ':' . $realm . ':' . $tablename . ':objects';
    }

    public function getKeyName($primaryKeys) {
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('Y-m-d H:i:s');
            }
        }
        return implode('|', $primaryKeys);
    }

    /**
     * @param $tablename
     * @return \Riak\Bucket
     */
    public function getBucket($realm, $tablename) {
        if(!isset($this->buckets[$tablename])) {
            $this->buckets[$tablename] = new \Riak\Bucket($this->connection, $this->_getBucketName($realm, $tablename));
        }

        return $this->buckets[$tablename];
    }
}