<?php


namespace AngryChimps\TaskBundle\services;

use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\NormBundle\realms\Norm\es\services\NormEsService;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\norm\services\NormRiakService;
use AngryChimps\TaskBundle\Services\Tasks\AbstractTask;

class TaskerService {
    protected $norm;
    protected $mysql;
    protected $es;
    protected $geoService;

    protected $tasks = [];

    public function __construct (NormRiakService $norm, NormMysqlService $mysql, NormEsService $es, GeolocationService $geoService) {
        $this->norm = $norm;
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
            $task->setServices($this->norm, $this->geoService);
            $task->execute();
        }
    }
}