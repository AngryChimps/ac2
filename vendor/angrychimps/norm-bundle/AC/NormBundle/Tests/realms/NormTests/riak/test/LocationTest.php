<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Company;
use NormTests\riak\Location;
use NormTests\riak\Member;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class LocationTest extends AbstractRiakTestCase {
    /**
     * @param Company $company
     * @return \NormTests\riak\Location
     */
    public static function getNewUnsavedObject(Company $company) {

        $location = new Location();
        $location->companyId = $company->id;
        $location->name = "Corporate Headquarters";

        return $location;
    }

    /**
     * @param Company $company
     * @return Location
     */
    public static function getNewSavedObject(Company $company) {
        $location = self::getNewUnsavedObject($company);
        $location->save();
        self::addObjectForCleanup($location);

        $company->locationIds[] = $location->id;
        $company->save();

        return $location;
    }
}