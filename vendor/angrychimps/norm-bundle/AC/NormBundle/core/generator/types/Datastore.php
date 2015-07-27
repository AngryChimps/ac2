<?php


namespace AC\NormBundle\core\generator\types;


class Datastore {
    public $name;
    public $driver;

    //Types
    public $isRiak2;
    public $isElasticsearch;

    //For Riak
    public $prefix;

    //For Elasticsearch
    public $indexName;
    public $shards;
    public $replicas;
    public $defaultAnalyzer;
}