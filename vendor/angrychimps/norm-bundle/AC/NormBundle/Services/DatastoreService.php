<?php


namespace AC\NormBundle\services;

use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\datastore\MysqlPdoDatastore;
use AC\NormBundle\core\datastore\RedisBlobDatastore;
use AC\NormBundle\core\datastore\RedisHashDatastore;
use AC\NormBundle\core\datastore\Riak2MapDatastore;
use AC\NormBundle\core\datastore\Riak1BlobDatastore;
use AC\NormBundle\core\datastore\ElasticsearchDatastore;
use Psr\Log\LoggerInterface;

class DatastoreService {
    protected static $debug;
    protected static $datastoreInfo;
    protected static $infoService;
    protected static $logger;

    private static $datastores = [];

    public function __construct($debug, $datastoreInfo, InfoService $infoService, LoggerInterface $logger) {
        self::$debug = $debug;
        self::$datastoreInfo = $datastoreInfo;
        self::$infoService = $infoService;
        self::$logger = $logger;
    }

    /**
     * @param $datastoreName
     * @return AbstractDatastore
     * @throws \Exception
     */
    public function getDatastore($datastoreName) {
        if(!isset(self::$datastores[$datastoreName])) {
            switch(self::$datastoreInfo[$datastoreName]['driver']) {
                case 'mysql':
                    self::$datastores[$datastoreName] = new MysqlPdoDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                case 'redis_blob':
                    self::$datastores[$datastoreName] = new RedisBlobDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                case 'redis_hash':
                    self::$datastores[$datastoreName] = new RedisHashDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                case 'riak_blob':
                    self::$datastores[$datastoreName] = new Riak1BlobDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                case 'riak2':
                    self::$datastores[$datastoreName] = new Riak2MapDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                case 'elasticsearch':
                    self::$datastores[$datastoreName] = new ElasticsearchDatastore(self::$datastoreInfo[$datastoreName], self::$infoService, self::$logger);
                    break;

                default:
                    throw new \Exception('Unsupported driver type: ' . self::$datastoreInfo[$datastoreName]['driver']);
            }
        }

        return self::$datastores[$datastoreName];
    }
}