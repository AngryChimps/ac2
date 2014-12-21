<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Service;
use NormTests\riak\Company;
use NormTests\riak\Member;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class CompanyTest extends AbstractRiakTestCase {
    /**
     * @return \NormTests\riak\Company
     */
    public static function getNewUnsavedObject(Member $member) {

        $company = new Company();
        $company->administerMemberIds[] = $member->id;
        $company->name = "Acme Drum Company";
        $company->description = "A bangin' good time";
        $company->plan = Company::BASIC_PLAN;
        $company->status = Company::ENABLED_STATUS;

        //Set up some services
        $service1 = new Service();
        $service1->name = 'Long Haircut';
        $service1->description = 'An excellent choice for long hair';
        $service1->discountedPrice = 49.99;
        $service1->originalPrice = 100;
        $service1->minsForService = 30;
        $service1->minsNotice = 60;
        $service1->status = Service::ENABLED_STATUS;

        return $company;
    }

    /**
     * @param Member $member
     * @return Company
     */
    public static function getNewSavedObject(Member $member) {
        $company = self::getNewUnsavedObject($member);
        $company->save();
        self::addObjectForCleanup($company);

        $member->managedCompanyIds[] = $company->id;
        $member->save();

        return $company;
    }
}