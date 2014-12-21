<?php


namespace AngryChimps\ApiBundle\Services;


use AC\NormBundle\Services\NormService;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Availability;
use Norm\riak\Calendar;
use Norm\riak\CalendarDay;
use Norm\riak\Location;
use Norm\riak\Member;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalendarService {
    /** @var  NormRiakService */
    protected $riak;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(NormRiakService $riak, NormMysqlService $mysql) {
        $this->riak = $riak;
        $this->mysql = $mysql;
    }

    public function createNew(Location $location, $name) {
        $calendar = new Calendar();
        $calendar->locationId = $location->id;
        $calendar->name = $name;
        $calendar->status = Calendar::ENABLED_STATUS;
        $calendar->companyId = $location->companyId;
        $this->riak->create($calendar);

        $location->calendarIds[] = $calendar->id;
        $this->riak->update($location);

        return $calendar;
    }

    public function addAvailability(Calendar $calendar, Availability $availability) {
        //If the availability overlaps a booking, then add it to the overlaps array
        $overlaps = array();
        foreach($calendar->bookings as $booking) {
            if(($availability->start < $booking->end && $availability->end > $booking->start)
                    || ($booking->start < $availability->end && $booking->end > $availability->start)) {
                $overlaps[] = $booking;
            }
        }

        //If any bookings overlap, return them so the FE can delete them first
        if(!empty($overlaps)) {
            return $overlaps;
        }

        //If the availability overlaps another availability, merge the two
        foreach($calendar->availabilities as $cdAvail) {
            if(($availability->start < $cdAvail->end && $availability->end > $cdAvail->start)
                || ($cdAvail->start < $availability->end && $cdAvail->end > $availability->start)) {
                $overlaps[] = $cdAvail;
            }
        }

        if(count($overlaps) == 0) {
            $calendar->availabilities[] = $availability;
        }
        else {
            for($i = 0; $i < count($overlaps); $i++) {
                list($newStart, $newEnd) = $this->mergeDateRanges($availability->start, $availability->end,
                    $overlaps[$i]->start, $overlaps[$i]->end);
                $availability->start = $newStart;
                $availability->end = $newEnd;
                unset($calendar->availabilities[$overlaps[$i]->id]);
            }
            $calendar->availabilities[] = $availability;
        }

        $this->riak->update($calendar);
    }

    protected function mergeDateRanges($start1, $end1, $start2, $end2) {
        $arr = array();
        $arr['start'] = ($start1 <= $start2) ? $start1 : $start2;
        $arr['end'] = ($end1 >= $end2) ? $end1 : $end2;
        return $arr;
    }
}