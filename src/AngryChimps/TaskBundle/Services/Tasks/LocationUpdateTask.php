<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\Location;

class LocationUpdateTask extends AbstractTask {
    protected $member;
    protected $changes;

    public function __construct(Location $location, array $changes) {
        $this->location = $location;
        $this->changes = $changes;
    }

    public function execute()
    {
        $this->updateMysqlObj($this->location, $this->changes);
    }
}