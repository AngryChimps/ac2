<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Calendar;
use NormTests\riak\Booking;
use NormTests\riak\Location;
use NormTests\riak\Availability;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class CalendarTest extends AbstractRiakTestCase {
    /**
     * @param Location $location
     * @return \NormTests\riak\Calendar
     */
    public static function getNewUnsavedObject(Location $location) {
        $calendar = new Calendar();
        $calendar->locationId = $location->id;
        $calendar->name = "Betty Sue's Calendar";

        $availability = new Availability();
        $availability->start = new \DateTime('2014-12-01 09:00:00');
        $availability->end = new \DateTime('2014-12-01 11:00:00');
        $calendar->availabilities[] = $availability;

        $booking = new Availability();
        $booking->title = 'Haircut for Joe';
        $booking->type = Booking::SHORT_BOOKING_TYPE;
        $booking->start = new \DateTime('2014-12-01 14:00:00');
        $booking->end = new \DateTime('2014-12-01 15:00:00');
        $calendar->bookings[] = $booking;

        return $calendar;
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