<?php


namespace AC\NormBundle\Services;

use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\core\datastore\EsDocumentDatastore;
use AC\NormBundle\core\datastore\Riak2MapDatastore;
use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Psr\Log\LoggerInterface;

class DatastoreService {
    protected $debug;
    protected $datastoreInfo;
    protected $infoService;
    protected $logger;
    protected $guzzleService;

    private $datastores = [];

    public function __construct($debug, $datastoreInfo, InfoService $infoService, LoggerInterface $logger,
        GuzzleService $guzzleService)
    {
        $this->debug = $debug;
        $this->datastoreInfo = $datastoreInfo;
        $this->infoService = $infoService;
        $this->logger = $logger;
        $this->guzzleService = $guzzleService;
    }

    /**
     * @param $datastoreName
     * @return AbstractDatastore
     * @throws \Exception
     */
    public function getDatastore($datastoreName) {
        if(!isset($this->datastores[$datastoreName])) {
            switch($this->datastoreInfo[$datastoreName]['driver']) {
//                case 'mysql':
//                    $this->datastores[$datastoreName] = new MysqlPdoDatastore($this->datastoreInfo[$datastoreName], $this->infoService, $this->logger, $this->guzzleService);
//                    break;
//
//                case 'redis_blob':
//                    $this->datastores[$datastoreName] = new RedisBlobDatastore($this->datastoreInfo[$datastoreName], $this->infoService, $this->logger, $this->guzzleService);
//                    break;
//
//                case 'redis_hash':
//                    $this->datastores[$datastoreName] = new RedisHashDatastore($this->datastoreInfo[$datastoreName], $this->infoService, $this->logger, $this->guzzleService);
//                    break;
//
//                case 'riak_blob':
//                    $this->datastores[$datastoreName] = new Riak1BlobDatastore($this->datastoreInfo[$datastoreName], $this->infoService, $this->logger, $this->guzzleService);
//                    break;

                case 'riak2':
                    $this->datastores[$datastoreName] = new Riak2MapDatastore(
                        $this->datastoreInfo[$datastoreName], $this->infoService, $this->logger, $this->guzzleService);
                    break;

                case 'elasticsearch':
                    $this->datastores[$datastoreName] = new EsDocumentDatastore(
                        $this->datastoreInfo[$datastoreName], $this->infoService, $this->logger);
                    break;

                default:
                    throw new \Exception('Unsupported driver type: ' . $this->datastoreInfo[$datastoreName]['driver']);
            }
        }

        return $this->datastores[$datastoreName];
    }
}