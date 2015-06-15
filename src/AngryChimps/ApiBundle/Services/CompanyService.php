<?php


namespace AngryChimps\ApiBundle\services;

use Norm\Member;
use Norm\Company;
use Norm\CompanyPhotos;
use Norm\CompanyServices;
use Norm\CompanyReviews;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AngryChimps\NormBundle\services\NormService;

class CompanyService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /** @var  NormService */
    protected $norm;

    public function __construct(ValidatorInterface $validator, NormService $norm) {
        $this->validator = $validator;
        $this->norm = $norm;
    }

    public function createEmpty(Member $member) {
        $company = new Company();
        $company->setStatus(Company::ENABLED_STATUS);
        $company->addToAdministerMemberIds($member->getId());
        $this->norm->create($company);

        $member->addToManagedCompanyIds($company->getId());
        $this->norm->update($member);

        $companyServices = new CompanyServices();
        $companyServices->setCompanyId($company->getId());
        $this->norm->create($companyServices);

        $companyReviews = new CompanyReviews();
        $companyReviews->setCompanyId($company->getId());
        $this->norm->create($companyReviews);

        $companyPhotos = new CompanyPhotos();
        $companyPhotos->setCompanyId($company->getId());
        $this->norm->create($companyPhotos);

        $companyServices = new CompanyServices();
        $companyServices->setCompanyId($company->getId());
        $this->norm->create($companyServices);
        return $company;
    }

    public function createCompany($name, Member $owner, &$errors) {
        $company = $this->createEmpty($owner);
        $company->setName($name);
        $company->addToAdministerMemberIds($owner->getId());


        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $this->norm->create($company);
        $owner->addToManagedCompanyIds($company->getId());
        $this->norm->update($owner);

        return $company;
    }

    public function updateCompany(Company $company, $name, &$errors) {
        $company->setName($name);

        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $this->norm->update($company);
        return true;
    }

    public function getByPk($pk) {
        return $this->norm->getCompany($pk);
    }

    public function markCompanyDeleted(Company $company) {
        $company->setStatus(Company::DISABLED_STATUS);
        $this->norm->update($company);
    }
}