<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Member;
use NormTests\riak\ProviderAd;
use NormTests\es\ProviderAdListing;
use NormTests\riak\Calendar;
use NormTests\riak\Company;
use NormTests\riak\Location;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class ProviderAdTest extends AbstractRiakTestCase {
    /**
     * @param Location $location
     * @return \NormTests\riak\Calendar
     */
    public static function getNewUnsavedObject(Member $member, Company $company, Location $location, ProviderAd $ad,
                                               Calendar $calendar) {
        $ad = new ProviderAd();
        $ad->locationId = $location->id;
        $ad->companyId = $company->id;
        $ad->calendarId = $calendar->id;
        $ad->authorId = $member->id;
        $ad->categoryId = 101;
        $ad->title = 'My ad title';
        $ad->description = 'My ad description';
        $ad->photos = array('photo1.jpg', 'photo2.png');

    }

    /**
     * @param Location $location
     * @return \NormTests\riak\Calendar
     */
    public static function getNewSavedObject(Location $location) {
        $calendar = self::getNewUnsavedObject($location);
        $calendar->save();
        self::addObjectForCleanup($calendar);

        $calendar->locationId = $location->id;
        $calendar->save();

        return $calendar;
    }

//    public function testSaveNew() {
//        $member = MemberTest::getNewSavedObject();
//        $company = CompanyTest::getNewSavedObject($member);
//        $location = LocationTest::getNewSavedObject($company);
//        $calendar = CalendarTest::getNewSavedObject($location);
//        $calendarDay = $this->getNewSavedObject($calendar);
//
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