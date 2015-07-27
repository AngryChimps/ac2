<?php


namespace AngryChimps\TaskBundle\services;

use AngryChimps\GeoBundle\Services\GeolocationService;
use AngryChimps\TaskBundle\Services\Tasks\AbstractTask;
use AngryChimps\NormBundle\services\NormService;

class TaskerService {
    protected $norm;
    protected $mysql;
    protected $es;
    protected $geoService;

    protected $tasks = [];

    public function __construct (NormService $norm, GeolocationService $geoService) {
        $this->norm = $norm;
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