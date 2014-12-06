<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\riak\Member;
use Norm\riak\Company;
use Norm\riak\CompanyServices;
use Norm\riak\CompanyReviews;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    public function createEmpty(Member $member) {
        $company = new Company();
        $company->status = Company::ENABLED_STATUS;
        $company->save();

        $member->managedCompanyIds[] = $company->id;
        $member->save();

        $company->administerMemberIds[] = $member->id;
        $company->save();

        $companyServices = new CompanyServices();
        $companyServices->companyId = $company->id;
        $companyServices->save();

        $companyReviews = new CompanyReviews();
        $companyReviews->companyId = $company->id;
        $companyReviews->save();

        return $company;
    }

    public function createCompany($name, Member $owner, &$errors) {
        $company = new Company();
        $company->name = $name;
        $company->administerMemberIds = array($owner->id);


        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $company->save();

        $owner->managedCompanyKeys[] = $company->id;
        $owner->save();

        return $company;
    }

    public function updateCompany($company, $name, &$errors) {
        $company->name = $name;

        $errors = $this->validator->validate($company);

        if(count($errors) > 0) {
            return false;
        }

        $company->save();
        return true;
    }
}