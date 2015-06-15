<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/19/14
 * Time: 10:05 AM
 */

namespace AC\NormBundle\core\datastore;


use AC\NormBundle\Services\RealmInfoService;
use Doctrine\DBAL\DriverManager;
use \Doctrine\DBAL\Configuration;
use \AC\NormBundle\core\datastore\MysqlTableDatastore;
use Psr\Log\LoggerInterface;

class DatastoreManager {
    /** @var \AC\NormBundle\core\datastore\AbstractDatastore[] */
    protected static $_datastores = array();

    protected static $_datastoreInfo = array();

    /** @var \doctrine\DBAL\Connection[] */
    protected static $_referenceDbs = array();

    public static function setDatastores($datastores) {
        self::$_datastoreInfo = $datastores;
    }

    /**
     * @param $datastoreName string
     * @param $realmInfo RealmInfoService
     * @return AbstractDatastore
     * @throws \Exception Unsupported driver type exception
     */
    public static function getDatastore($datastoreName, RealmInfoService $realmInfo, LoggerInterface $loggerService) {
        if(!array_key_exists($datastoreName, self::$_datastores)) {
            $configParams = self::$_datastoreInfo[$datastoreName];


            switch($configParams['driver']) {
                case 'mysql':
                    self::$_datastores[$datastoreName] = new MysqlPdoDatastore($configParams, $realmInfo, $loggerService);
                    break;

                case 'redis_blob':
                    self::$_datastores[$datastoreName] = new RedisBlobDatastore($configParams, $realmInfo, $loggerService);
                    break;

                case 'redis_hash':
                    self::$_datastores[$datastoreName] = new RedisHashDatastore($configParams, $realmInfo, $loggerService);
                    break;

                case 'riak_blob':
                    self::$_datastores[$datastoreName] = new Riak1BlobDatastore($configParams, $realmInfo, $loggerService);
                    break;

                case 'riak_map':
                    self::$_datastores[$datastoreName] = new Riak2MapDatastore($configParams, $realmInfo, $loggerService);
                    break;

                case 'elasticsearch':
                    self::$_datastores[$datastoreName] = new ElasticsearchDatastore($configParams, $realmInfo, $loggerService);
                    break;

                default:
                    throw new \Exception('Unsupported driver type: ' . $configParams['driver']);
            }
        }

        return self::$_datastores[$datastoreName];
    }

    /**
     * @param $realm
     * @return AbstractDatastore
     */
    public static function getReferenceDatastore($realm) {
        if(!array_key_exists($realm, self::$_referenceDbs)) {
            $configParams = Config::$realms[$realm]['referenceDatastore'];

            switch($configParams['driver']) {
                case 'mysql':
                    self::$_referenceDbs[$realm] = new MysqlPdoDatastore($configParams);
                    break;
            }
        }

        return self::$_referenceDbs[$realm];
    }

//    /**
//     * @param $realm
//     * @return \doctrine\DBAL\Connection
//     */
//    public static function getReferenceDb($realm) {
//        if(!array_key_exists($realm, self::$_referenceDbs)) {
//            $configParams = Config::$realms[$realm]['referenceDatastore'];
//            $config = new Configuration();
//
//            switch($configParams['driver']) {
//                case 'pdo_mysql':
//                    self::$_referenceDbs[$realm] = DriverManager::getConnection($configParams, $config);
//                    break;
//            }
//        }
//
//        return self::$_referenceDbs[$realm];
//    }

    public static function getReferenceDbType($realm) {
        return Config::$realms[$realm]['referenceDatastore']['driver'];
    }
}