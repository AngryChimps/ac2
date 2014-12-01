<?php

namespace AC\NormBundle\Tests\realms\riak;

use NormTests\riak\Calendar;
use NormTests\riak\Location;
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
}