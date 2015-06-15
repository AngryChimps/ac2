<?php


namespace AC\NormBundle\core;

use Riak\Client\Core\Query\RiakLocation;
use Riak\Client\Core\Query\RiakNamespace;
use Riak\Client\Command\DataType\StoreMap;
use Riak\Client\Command\DataType\Builder\StoreMapBuilder;
use Riak\Client\Command\DataType\SetUpdate;
use Riak\Client\Command\DataType\Response\FetchMapResponse;
use AC\NormBundle\core\interfaces\NormObjectInterface;

class NormObject implements NormObjectInterface {
    /** @var StoreMapBuilder */
    protected $riakStoreMapBuilder;

    /** @var  FetchMapResponse */
    protected $riakFetchMap;

    /** @var  array */
    protected $mapValues = [];

    public function __construct() {
        //Set defaults if necessary


        $this->riakStoreMapBuilder = StoreMap::builder();
    }

    /** @returns StoreMapBuilder */
    public function getRiakStoreMapBuilder() {
        return $this->riakStoreMapBuilder;
    }

    public function setRiakStoreMapBuilder(StoreMapBuilder $storeMapBuilder) {
        $this->riakStoreMapBuilder = $storeMapBuilder;
    }

    /** @returns FetchMapResponse */
    public function getRiakFetchMap() {
        return $this->riakFetchMap;
    }

    public function setRiakFetchMap(FetchMapResponse $map) {
        $this->riakFetchMap = $map;
    }

    public function getRegister1() {
        if(!isset($this->mapValues['register1'])) {
            $this->mapValues['register1'] = $this->getRiakFetchMap()->map('register1');
        }
        return $this->mapValues['register1'];
    }

    public function setRegister1($val) {
        $this->mapValues['register1'] = $val;
        $this->riakStoreMapBuilder->updateRegister('register1', $val);
    }
}