<?php


namespace AngryChimps\TaskBundle\Services;

use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use AngryChimps\TaskBundle\Services\Tasks\AbstractTask;

class TaskerService {
    protected $riak;
    protected $mysql;
    protected $es;
    protected $geoService;

    protected $tasks = [];

    public function __construct (NormRiakService $riak, NormMysqlService $mysql, NormEsService $es, GeolocationService $geoService) {
        $this->riak = $riak;
        $this->mysql = $mysql;
        $this->es = $es;
        $this->geoService = $geoService;
    }

    public function store(AbstractTask $task) {
        $this->tasks[] = $task;
    }

    public function runTasks($logger) {
        $logger->info('running tasks');
        foreach($this->tasks as $task) {
            $logger->info('Running...');
            $task->setServices($this->riak, $this->mysql, $this->es, $this->geoService);
            $task->execute();
        }
    }
}