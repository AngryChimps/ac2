<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Availability;
use NormTests\riak\Booking;
use NormTests\riak\Calendar;
use NormTests\riak\CalendarDay;
use NormTests\riak\Member;
use AC\NormBundle\Tests\AbstractRiakTestCase;

class CalendarDayTest extends AbstractRiakTestCase {
    /**
     * @param Calendar $calendar
     * @return \NormTests\riak\CalendarDay
     */
    public static function getNewUnsavedObject(Calendar $calendar) {
        $calendarDay = new CalendarDay();
        $calendarDay->calendarId = $calendar->id;
        $calendarDay->date = new \DateTime('2014-12-01');

        $availability = new Availability();
        $availability->start = new \DateTime('2014-12-01 09:00:00');
        $availability->end = new \DateTime('2014-12-01 11:00:00');
        $calendarDay->availabilities[] = $availability;

        $booking = new Availability();
        $booking->title = 'Haircut for Joe';
        $booking->type = Booking::SHORT_BOOKING_TYPE;
        $booking->start = new \DateTime('2014-12-01 14:00:00');
        $booking->end = new \DateTime('2014-12-01 15:00:00');
        $calendarDay->bookings[] = $booking;

        return $calendarDay;
    }

    /**
     * @param Calendar $calendar
     * @return \NormTests\riak\CalendarDay
     */
    public static function getNewSavedObject(Calendar $calendar) {
        $calendarDay = self::getNewUnsavedObject($calendar);
        $calendarDay->save();
        self::addObjectForCleanup($calendarDay);

        return $calendarDay;
    }

    public function testSaveNew() {
        $member = MemberTest::getNewSavedObject();
        $company = CompanyTest::getNewSavedObject($member);
        $location = LocationTest::getNewSavedObject($company);
        $calendar = CalendarTest::getNewSavedObject($location);
        $calendarDay = $this->getNewSavedObject($calendar);

        $bucket = $this->getObjectsBucket('calendar_day');
        $response = $bucket->get($this->getKeyName($calendarDay->getPrimaryKeyData()));

        if(!empty($response)) {
            assertTrue($response->hasObject());

            $content = $response->getFirstObject();
            $dbObj = $content->getContent();
            $dbObjDecompressed = json_decode($dbObj, true);

            assertEquals($dbObjDecompressed['calendar_id'], $calendarDay->calendarId);
        }
    }
}