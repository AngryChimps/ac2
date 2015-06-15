<?php


namespace AngryChimps\TaskBundle\Services\Tasks;


use Norm\riak\Company;

class CompanyCreateTask extends AbstractTask {
    protected $company;

    public function __construct(Company $company) {
        $this->company = $company;
    }

    public function execute()
    {
        $this->createMysqlObj($this->company);

        //Create company_member relationship in mysql

    }
}