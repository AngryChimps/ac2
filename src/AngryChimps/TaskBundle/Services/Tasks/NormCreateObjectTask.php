<?php


namespace AngryChimps\TaskBundle\Services\Tasks;

use AC\NormBundle\core\datastore\AbstractDatastore;
use AC\NormBundle\Services\NormCrudService;
use AngryChimps\NormBundle\services\NormService;

class NormCreateObjectTask extends AbstractTask {
    protected $obj;

    /** NormService */
    protected $norm;

    protected $dsName;

    public function __construct($obj, NormCrudService $norm, $dsName) {
        $this->obj = $obj;
        $this->norm = $norm;
        $this->dsName = $dsName;
    }

    public function execute()
    {
        $this->norm->create($this->obj, $this->dsName);
    }
}