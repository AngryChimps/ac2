<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\Company;

class CompanyUpdateTask extends AbstractTask {
    protected $company;
    protected $changes;

    public function __construct(Company $company, array $changes) {
        $this->company = $company;
        $this->changes = $changes;
    }

    public function execute()
    {
        $this->updateMysqlObj($this->company, $this->changes);
    }
}