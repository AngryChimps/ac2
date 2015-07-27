<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\Location;

class LocationCreateTask extends AbstractTask {
    protected $member;

    public function __construct(Location $location) {
        $this->location = $location;
    }

    public function execute()
    {
        $this->createMysqlObj($this->location);
    }
}