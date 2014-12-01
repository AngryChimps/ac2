<?php

namespace AC\NormBundle\Tests\realms\riak;

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