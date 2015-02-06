<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

abstract class AbstractTask {
    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    /** @var  NormEsService */
    protected $es;

    /** @var  GeolocationService */
    protected $geoService;

    public function setServices(NormRiakService $riak, NormMysqlService $mysql, NormEsService $es, GeolocationService $geoService) {
        $this->riak = $riak;
        $this->mysql = $mysql;
        $this->es = $es;
        $this->geoService = $geoService;
    }
}