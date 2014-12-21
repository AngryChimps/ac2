<?php

namespace AC\NormBundle\Tests\realms\riak;

use AC\NormBundle\Tests\AbstractElasticsearchTestCase;
use NormTests\riak\ProviderAd;
use NormTests\es\ProviderAdListing;
use NormTests\riak\Calendar;
use NormTests\riak\Company;
use NormTests\riak\Location;

class ProviderAdListingTest extends AbstractElasticsearchTestCase {
    /**
     * @return \NormTests\es\ProviderAdListing
     */
    public static function getNewUnsavedObject(Company $company, Location $location, ProviderAd $ad,
                                               Calendar $calendar)
    {
        $listing = new ProviderAdListing();
        $listing->adId = $ad->currentImmutableId;
        $listing->companyName = $company->name;
        $listing->title = $ad->title;
        $listing->photo = $ad->photos[0];
        $listing->address = $location->address;
        $listing->rating = $company->ratingAvg;
        $listing->availabilities = $calendar->availabilities;

        return $listing;
    }

    /**
     * @return \NormTests\es\ProviderAdListing
     */
    public static function getNewSavedObject(Company $company, Location $location, ProviderAd $ad,
                                             Calendar $calendar) {
        $listing = self::getNewUnsavedObject($company, $location, $ad, $calendar);
        $listing->save();
        self::addObjectForCleanup($listing);

        return $calendar;
    }

//    public function testSaveNew() {
//        $member = MemberTest::getNewSavedObject();
//        $company = CompanyTest::getNewSavedObject($member);
//        $location = LocationTest::getNewSavedObject($company);
//        $calendar = CalendarTest::getNewSavedObject($location);
////        $ad =
//        $bucket = $this->getObjectsBucket('calendar_day');
//        $response = $bucket->get($this->getKeyName($calendarDay->getPrimaryKeyData()));
//
//        if(!empty($response)) {
//            assertTrue($response->hasObject());
//
//            $content = $response->getFirstObject();
//            $dbObj = $content->getContent();
//            $dbObjDecompressed = json_decode($dbObj, true);
//
//            assertEquals($dbObjDecompressed['calendar_id'], $calendarDay->calendarId);
//        }
//    }

}