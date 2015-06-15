<?php


namespace AC\NormBundle\core\datastore;

use Riak\Client\RiakClient;
use Riak\Client\RiakClientBuilder;
use AC\NormBundle\Services\RealmInfoService;
use Psr\Log\LoggerInterface;

abstract class AbstractRiak2Datastore extends AbstractDatastore {
    /** @var  RiakClient */
    protected static $riakClient;

    protected static $riakNamespacePrefix;

    public function __construct($configParams, RealmInfoService $realmInfo,
                                LoggerInterface $loggerService) {
        if(self::$riakClient === null) {
            self::$riakClient = self::getRiakClient($configParams);
        }

        parent::__construct($realmInfo, $loggerService);

        self::$riakNamespacePrefix = $configParams['prefix'];
    }

    protected static function getRiakClient($configParams) {
        if(self::$riakClient === null) {
            $builder = new RiakClientBuilder();
            foreach($configParams['servers'] as $server) {
                $builder->withNodeUri('proto://' . $server['host'] . ':' . $server['port']);
            }
            self::$riakClient = $builder->build();
        }
        return self::$riakClient;
    }

    protected static function getKeyAsString($primaryKeys) {
        if(!is_array($primaryKeys)) {
            $primaryKeys = [$primaryKeys];
        }
        foreach($primaryKeys as &$primaryKey) {
            if($primaryKey instanceof \DateTime) {
                $primaryKey = $primaryKey->format('c');
            }
        }
        return implode('|', $primaryKeys);
    }

}