<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\riak\Member;
use Norm\riak\Company;
use Norm\riak\CompanyPhotos;
use Norm\riak\CompanyServices;
use Norm\riak\CompanyReviews;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AC\NormBundle\Services\NormService;
use Norm\riak\ServiceCollection;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;

class CompanyService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(ValidatorInterface $validator, NormRiakService $riak, NormMysqlService $mysql) {
        $this->validator = $validator;
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function createEmpty(Member $member) {
        $company = new Company();
        $company->status = Company::ENABLED_STATUS;
        $company->administerMemberIds[] = $member->id;
        $this->riak->create($company);

        $member->managedCompanyIds[] = $company->id;
        $this->riak->update($member);

        $companyServices = new CompanyServices();
        $companyServices->companyId = $company->id;
        $this->riak->create($companyServices);

        $companyReviews = new CompanyReviews();
        $companyReviews->companyId = $company->id;
        $this->riak->create($companyReviews);

        $companyPhotos = new CompanyPhotos();
        $companyPhotos->companyId = $company->id;
        $companyPhotos->photos = array();
        $this->riak->create($companyPhotos);

        $companyServices = new CompanyServices();
        $companyServices->companyId = $company->id;
        $companyServices->services = new ServiceCollection();
        $this->riak->create($companyServices);
        return $company;
    }

    public function createCompany($name, Member $owner, &$errors) {
        $company = $this->createEmpty($owner);
        $company->name = $name;
        $company->administerMemberIds = array($owner->id);


        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $this->riak->create($company);

        $owner->managedCompanyIds[] = $company->id;
        $this->riak->update($owner);

        return $company;
    }

    public function updateCompany(Company $company, $name, &$errors) {
        $company->name = $name;

        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $this->riak->update($company);
        return true;
    }

    public function getByPk($pk) {
        return $this->riak->getCompany($pk);
    }

    public function markCompanyDeleted(Company $company) {
        $company->status = Company::DISABLED_STATUS;
        $this->riak->update($company);
    }
}