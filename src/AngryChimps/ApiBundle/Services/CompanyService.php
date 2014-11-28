<?php


namespace AngryChimps\ApiBundle\Services;


use Norm\riak\Member;
use Norm\riak\Company;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CompanyService {
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    public function createCompany($name, Member $owner, &$errors) {
        $company = new Company();
        $company->name = $name;
        $company->administerMemberIds = array($owner->id);


//        $errors = $this->validator->validate($company);
//
//        if(count($errors) > 0) {
//            return false;
//        }

        $company->save();

        $owner->managedCompanyKeys[] = $company->id;
        $owner->save();

        return $company;
    }
}