<?php


namespace AC\NormBundle\core\datastore;

use AngryChimps\GuzzleBundle\Services\GuzzleService;
use Riak\Client\RiakClient;
use Riak\Client\RiakClientBuilder;
use AC\NormBundle\services\InfoService;
use Psr\Log\LoggerInterface;

abstract class AbstractRiak2Datastore extends AbstractDatastore {
    /** @var  RiakClient */
    protected $riakClient;

    protected $riakNamespacePrefix;

    protected $configParams;

    protected $guzzleService;

    public function __construct($configParams, InfoService $infoService,
                                LoggerInterface $loggerService, GuzzleService $guzzleService) {
        if($this->riakClient === null) {
            $this->riakClient = $this->getRiakClient($configParams);
        }

        parent::__construct($infoService, $loggerService);

        $this->riakNamespacePrefix = $configParams['prefix'];
        $this->configParams = $configParams;
        $this->guzzleService = $guzzleService;
    }

    protected function getRiakClient($configParams) {
        if($this->riakClient === null) {
            $builder = new RiakClientBuilder();
            foreach($configParams['servers'] as $server) {
                $builder->withNodeUri('proto://' . $server['host'] . ':' . $server['port']);
            }
            $this->riakClient = $builder->build();
        }
        return $this->riakClient;
    }

    protected function getKeyAsString($primaryKeys) {
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