<?php


namespace AC\NormBundle\Services;

use AC\NormBundle\core\datastore\MysqlPdoDatastore;
use AC\NormBundle\core\datastore\RedisBlobDatastore;
use AC\NormBundle\core\datastore\RedisHashDatastore;
use AC\NormBundle\core\datastore\RiakBlobDatastore;
use AC\NormBundle\core\datastore\ElasticsearchDatastore;
use Psr\Log\LoggerInterface;

class DatastoreService {
    protected static $debug;
    protected static $datastoreInfo;
    protected static $realmInfo;
    protected static $logger;

    private static $datastores = [];

    public function __construct($debug, $datastoreInfo, RealmInfoService $realmInfoService, LoggerInterface $logger) {
        self::$debug = $debug;
        self::$datastoreInfo = $datastoreInfo;
        self::$realmInfo = $realmInfoService;
        self::$logger = $logger;
    }

    public function getDatastore($datastoreName) {
        if(!isset(self::$datastores[$datastoreName])) {
            switch(self::$datastoreInfo[$datastoreName]['driver']) {
                case 'mysql':
                    self::$datastores[$datastoreName] = new MysqlPdoDatastore(self::$datastoreInfo[$datastoreName], self::$realmInfo, self::$logger);
                    break;

                case 'redis_blob':
                    self::$datastores[$datastoreName] = new RedisBlobDatastore(self::$datastoreInfo[$datastoreName], self::$realmInfo, self::$logger);
                    break;

                case 'redis_hash':
                    self::$datastores[$datastoreName] = new RedisHashDatastore(self::$datastoreInfo[$datastoreName], self::$realmInfo, self::$logger);
                    break;

                case 'riak_blob':
                    self::$datastores[$datastoreName] = new RiakBlobDatastore(self::$datastoreInfo[$datastoreName], self::$realmInfo, self::$logger);
                    break;

                case 'elasticsearch':
                    self::$datastores[$datastoreName] = new ElasticsearchDatastore(self::$datastoreInfo[$datastoreName], self::$realmInfo, self::$logger);
                    break;

                default:
                    throw new \Exception('Unsupported driver type: ' . self::$datastoreInfo[$datastoreName]['driver']);
            }
        }

        return self::$datastores[$datastoreName];
    }
}