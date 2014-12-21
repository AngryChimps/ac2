<?php


namespace AngryChimps\ApiBundle\Services;


class ElasticsearchService {
    /** @var  \Elastica\Client */
    private $client;

    /** @var  \Elastica\Index */
    private $index;

    public function __construct($configParams) {
        $this->client = new Client(array('servers' => $configParams['servers']));
        $this->index = $this->client->getIndex($configParams['index_name']);
    }

    public function getIndex($realm, $tableName) {
        return $this->index;
    }
} 