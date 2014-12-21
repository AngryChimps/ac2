<?php


namespace AC\NormBundle\core\datastore;

use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

abstract class AbstractRiakDatastore extends AbstractDatastore {
    const PREFIX = '__norm';

    /** @var \Riak\Connection  */
    public $connection;

    /** @var \Riak\Bucket[] */
    protected $buckets = array();

    /** @var  RealmInfoService */
    protected $realmInfo;



    public function __construct($configParams, RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        parent::__construct($realmInfo, $loggerService);
        $this->connection = new \Riak\Connection($configParams['host'], $configParams['port']);
        $this->realmInfo = $realmInfo;
    }

    protected function _getBucketName($realm, $tablename) {
        return self::PREFIX . ':' . $realm . ':' . $tablename . ':objects';
    }

    protected function getKeyName($primaryKeys) {
        if(!is_array($primaryKeys)) {
            $primaryKeys = [$primaryKeys];
        }
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