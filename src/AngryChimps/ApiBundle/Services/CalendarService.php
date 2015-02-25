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

    public function getCalendar($calendarId) {
        return $this->riak->getCalendar($calendarId);
    }

    public function getData(Calendar $calendar, $isOwner = false) {
        $arr = [];
        $arr['name'] = $calendar->name;

        foreach($calendar->availabilities as $availability) {
            $arr2 = [];
            $arr2['start'] = $availability->start;
            $arr2['end'] = $availability->end;
            $arr['availabilities'][] = $arr2;
        }

        if($isOwner) {
            foreach ($calendar->bookings as $booking) {
                $arr2 = [];
                $arr2['title'] = $booking->title;
                $arr2['start'] = $booking->start;
                $arr2['end'] = $booking->end;

                if (!empty($booking->bookingDetailId)) {
                    $arr2['booking_detail_id'] = $booking->bookingDetailId;
                }
                $arr['bookings'][] = $arr2;
            }
        }

        return $arr;
    }

    public function markDeleted(Calendar $calendar) {
        $calendar->status = Calendar::DISABED_STATUS;
        $this->riak->update($calendar);
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

    public function update(Calendar $calendar, $changes) {
        foreach($changes as $field => $value) {
            switch($field) {
                case 'name':
                    $calendar->name = $value;
                    break;
                default:
                    return false;
            }
        }

        $this->riak->update($calendar);
        return true;
    }

    public function addAvailability(Calendar $calendar, Availability $newAvailability) {
        //If the availability overlaps a booking, return false
        $overlaps = array();
        foreach($calendar->bookings as $booking) {
            if(($newAvailability->start < $booking->end && $newAvailability->end > $booking->start)
                    || ($booking->start < $newAvailability->end && $booking->end > $newAvailability->start)) {
               return false;
            }
        }

        $availabilities = [];
        $ignoreNewAvailability = false;
        foreach($calendar->availabilities as $availability) {
            //If the new availability is encompassed by another availability, ignore the new availability
            if($availability->start <= $newAvailability->start && $availability->end >= $newAvailability->end) {
                $ignoreNewAvailability = true;
                $availabilities[] = $availability;
            }
            //If the new availability encompasses another availability, ignore the other availability
            elseif($availability->start >= $newAvailability->start && $availability->end <= $newAvailability->end) {
                //Just ignore the new availability
            }
            //If there is no overlap, add to the $availabilities array to save unmodified
            elseif($availability->end < $newAvailability->start || $availability->start > $newAvailability->end) {
                $availabilities[] = $availability;
            }
            //If the end of the availability overlaps the start of the new availability, merge the two
            elseif($availability->start < $newAvailability->start && $availability->end >= $newAvailability->start) {
                $newAvailability->start = $availability->start;
            }
            //If the start of the availability overlaps the end of the new availability, merge the two
            elseif($availability->start <= $newAvailability->end && $availability->end > $newAvailability->start) {
                $newAvailability->end = $availability->end;
            }
            else {
                throw new \Exception('Unable to add availability; unknown overlap');
            }
        }

        if(!$ignoreNewAvailability) {
            $availabilities[] = $newAvailability;
        }

        $calendar->availabilities = $availabilities;
        $this->riak->update($calendar);

        return true;
    }

    public function removeAvailability(Calendar $calendar, Availability $availabilityToRemove) {
        $availabilities = [];
        foreach($calendar->availabilities as $availability) {
            //If the availability is completely before or after the one to remove, keep it
            if($availability->end <= $availabilityToRemove->start || $availability->start >= $availabilityToRemove->end) {
                $availabilities[] = $availability;
            }
            //If the availability is encompassed by the one to remove (or is the same), skip it
            elseif($availability->start >= $availabilityToRemove->start && $availability->end <= $availabilityToRemove->end) {
                //Skip it
            }
            //If the availability fully encompassses the one to remove, split it into two
            elseif($availability->start < $availabilityToRemove->start && $availability->end > $availabilityToRemove->end) {
                $a1 = new Availability();
                $a1->start = $availability->start;
                $a1->end = $availabilityToRemove->start;
                $availabilities[] = $a1;

                $a2 = new Availability();
                $a2->start = $availabilityToRemove->end;
                $a2->end = $availability->end;
                $availabilities[] = $a2;
            }
            //If the end of the availability overlaps the start of the one to remove, adjust it's end date
            elseif($availability->end > $availabilityToRemove->start && $availability->start < $availabilityToRemove->start) {
                $a1 = new Availability();
                $a1->start = $availability->start;
                $a1->end = $availabilityToRemove->start;
                $availabilities[] = $a1;
            }
            //If the start of the availability overlaps the end of the one to remove, adjust it's start date
            elseif($availability->start < $availabilityToRemove->end && $availability->end > $availabilityToRemove->end) {
                $a2 = new Availability();
                $a2->start = $availabilityToRemove->end;
                $a2->end = $availability->end;
                $availabilities[] = $a2;
            }
            else {
                throw new \Exception('Unable to delete availability; unknown overlap');
            }
        }

        $calendar->availabilities = $availabilities;
        $this->riak->update($calendar);
    }

    /**
     * @param $availabilities Availability[]
     * @param $minsForServices int
     * @return \DateTime[]
     */
    public function getAvailableStartTimes($availabilities, $minsForServices) {
        $startTimes = [];

        /** @var Availability $availability */
        foreach($availabilities as $availability) {
            $lastStartTime = $availability->end->sub(new \DateInterval('PT' . $minsForServices . 'M'));

            if($lastStartTime >= $availability->start) {
                for ($start = $availability->start;
                     $start <= $lastStartTime;
                     $start = $start->add(new \DateInterval('PT15M'))) {
                    $startTimes[] = $start->format('c');
                }
            }
        }
        return $startTimes;
    }
}