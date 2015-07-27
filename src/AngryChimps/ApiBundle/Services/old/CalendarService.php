<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\NormBundle\services\NormService;
use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use AngryChimps\NormBundle\realms\Norm\mysql\services\NormMysqlService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\Availability;
use Norm\Calendar;
use Norm\Location;
use Norm\Member;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalendarService {
    /** @var  NormService */
    protected $norm;

    /** @var  NormMysqlService */
    protected $mysql;

    public function __construct(NormService $norm) {
        $this->norm = $norm;
    }

    /**
     * @param $calendarId
     * @return Calendar
     */
    public function getCalendar($calendarId) {
        return $this->norm->getCalendar($calendarId);
    }

    public function getData(Calendar $calendar, $isOwner = false) {
        $arr = [];
        $arr['id'] = $calendar->getId();
        $arr['name'] = $calendar->getName();

        foreach($calendar->getAvailabilities() as $availability) {
            $arr2 = [];
            $arr2['start'] = $availability->getStart()->format('c');
            $arr2['end'] = $availability->getEnd()->format('c');
            $arr['availabilities'][] = $arr2;
        }

        if($isOwner) {
            foreach ($calendar->getBookings() as $booking) {
                $arr2 = [];
                $arr2['title'] = $booking->getTitle();
                $arr2['start'] = $booking->getStart()->format('c');
                $arr2['end'] = $booking->getEnd()->format('c');

                if (!empty($booking->bookingDetailId)) {
                    $arr2['booking_detail_id'] = $booking->bookingDetailId;
                }
                $arr['bookings'][] = $arr2;
            }
        }

        return ['calendar' => $arr];
    }

    public function markDeleted(Calendar $calendar) {
        $calendar->setStatus(Calendar::DISABED_STATUS);
        $this->norm->update($calendar);
    }
    public function update(Calendar $calendar, $changes) {
        foreach($changes as $field => $value) {
            switch($field) {
                case 'name':
                    $calendar->setName($value);
                    break;
                default:
                    return false;
            }
        }

        $this->norm->update($calendar);
        return true;
    }

    public function addAvailability(Calendar $calendar, Availability $newAvailability) {
        //If the availability overlaps a booking, return false
        $overlaps = array();
        foreach($calendar->getBookings() as $booking) {
            if(($newAvailability->getStart() < $booking->getEnd() && $newAvailability->getEnd() > $booking->getStart())
                || ($booking->getStart() < $newAvailability->getEnd() && $booking->getEnd() > $newAvailability->getStart())) {
                return false;
            }
        }

        $availabilities = [];
        $ignoreNewAvailability = false;
        foreach($calendar->getAvailabilities() as $availability) {
            //If the new availability is encompassed by another availability, ignore the new availability
            if($availability->getStart() <= $newAvailability->getStart() && $availability->getEnd() >= $newAvailability->getEnd()) {
                $ignoreNewAvailability = true;
                $availabilities[] = $availability;
            }
            //If the new availability encompasses another availability, ignore the other availability
            elseif($availability->getStart() >= $newAvailability->getStart() && $availability->getEnd() <= $newAvailability->getEnd()) {
                //Just ignore the new availability
            }
            //If there is no overlap, add to the $availabilities array to save unmodified
            elseif($availability->getEnd() < $newAvailability->getStart() || $availability->getStart() > $newAvailability->getEnd()) {
                $availabilities[] = $availability;
            }
            //If the end of the availability overlaps the start of the new availability, merge the two
            elseif($availability->getStart() < $newAvailability->getStart() && $availability->getEnd() >= $newAvailability->getStart()) {
                $newAvailability->setStart($availability->getStart());
            }
            //If the start of the availability overlaps the end of the new availability, merge the two
            elseif($availability->getStart() <= $newAvailability->getEnd() && $availability->getEnd() > $newAvailability->getStart()) {
                $newAvailability->setEnd($availability->getEnd());
            }
            else {
                throw new \Exception('Unable to add availability; unknown overlap');
            }
        }

        if(!$ignoreNewAvailability) {
            $availabilities[] = $newAvailability;
        }

        $calendar->updateAvailabilities($availabilities);
        $this->norm->update($calendar);

        return true;
    }

    public function createNew(Location $location, $name) {
        $calendar = new Calendar();
        $calendar->setLocationId($location->getId());
        $calendar->setName($name);
        $calendar->setStatus(Calendar::ENABLED_STATUS);
        $calendar->setCompanyId($location->getCompanyId());
        $this->norm->create($calendar);

        $location->addToCalendarIds($calendar->getId());
        $this->norm->update($location);

        return $calendar;
    }

    public function removeAvailability(Calendar $calendar, Availability $availabilityToRemove) {
        $availabilities = [];
        foreach($calendar->getAvailabilities() as $availability) {
            //If the availability is completely before or after the one to remove, keep it
            if($availability->getEnd() <= $availabilityToRemove->getStart() || $availability->getStart() >= $availabilityToRemove->getEnd()) {
                $availabilities[] = $availability;
            }
            //If the availability is the same, skip it
            elseif($availability->getStart() == $availabilityToRemove->getStart() && $availability->getEnd() == $availabilityToRemove->getEnd()) {
                //Skip it
            }
            //If the availability is encompassed by the one to remove, then part of that the is already booked
            elseif($availability->getStart() >= $availabilityToRemove->getStart() && $availability->getEnd() <= $availabilityToRemove->getEnd()) {
                throw new \Exception('Booking time is not available');
            }
            //If the availability fully encompassses the one to remove, split it into two
            elseif($availability->getStart() < $availabilityToRemove->getStart() && $availability->getEnd() > $availabilityToRemove->getEnd()) {
                $a1 = new Availability();
                $a1->setStart($availability->getStart());
                $a1->setEnd($availabilityToRemove->getStart());
                $availabilities[] = $a1;

                $a2 = new Availability();
                $a2->setStart($availability->getEnd());
                $a2->setEnd($availabilityToRemove->getEnd());
                $availabilities[] = $a2;
            }
            //If the end of the availability overlaps the start of the one to remove, adjust it's end date
            elseif($availability->getEnd() > $availabilityToRemove->getStart() && $availability->getStart() < $availabilityToRemove->getStart()) {
                $a1 = new Availability();
                $a1->setStart($availability->getStart());
                $a1->setEnd($availabilityToRemove->getStart());
                $availabilities[] = $a1;
            }
            //If the start of the availability overlaps the end of the one to remove, adjust it's start date
            elseif($availability->getStart() < $availabilityToRemove->getEnd() && $availability->getEnd() > $availabilityToRemove->getEnd()) {
                $a2 = new Availability();
                $a2->setStart($availability->getEnd());
                $a2->setEnd($availabilityToRemove->getEnd());
                $availabilities[] = $a2;
            }
            else {
                throw new \Exception('Unable to delete availability; unknown overlap');
            }
        }

        $calendar->updateAvailabilities($availabilities);
        $this->norm->update($calendar);
    }

    /**
     * @param $availabilities Availability[]
     * @param $minsForServices int This is the number of minutes for the service plus notice required
     * @return \DateTime[]
     */
    public function getAvailableStartTimes($availabilities, $minsForServices) {
        $startTimes = [];

        /** @var Availability $availability */
        foreach($availabilities as $availability) {
            $lastStartTime = $availability->getEnd()->sub(new \DateInterval('PT' . ($minsForServices) . 'M'));

            if($lastStartTime >= $availability->getStart()) {
                for ($start = $availability->getStart();
                     $start <= $lastStartTime;
                     $start = $start->add(new \DateInterval('PT15M'))) {
                    $startTimes[] = $start;
                }
            }
        }
        return $startTimes;
    }

    public function getAvailableTimeWindows($availabilities, $minsForService, $minsNotice)
    {
        $windows = [];

        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            $startTime = $availability->getStart();
            $endTime = $availability->getEnd();

            if($startTime->add(new \DateInterval('PT' . $minsNotice . 'M')) > new \DateTime('now')) {
                $startTime = new \DateTime('now');
                $startTime->add(new \DateInterval('PT' . $minsNotice . 'M'));
            }

            if($endTime->sub(new \DateInterval('PT' . $minsForService . 'M')) <= $startTime) {
                continue;
            }
            else {
                $window = [];
                $window['start'] = $availability->getStart()->format('c');
                $window['end'] = $availability->getEnd()->format('c');
                $windows[] = $window;
            }
        }

        return ['availabilities' => $windows ];
    }
}