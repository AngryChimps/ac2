<?php


namespace AngryChimps\ApiBundle\Services;


use AngryChimps\MailerBundle\Messages\BasicMessage;
use AngryChimps\MailerBundle\Services\MailerService;
use Armetiz\FacebookBundle\FacebookSessionPersistence;
use Norm\riak\Calendar;
use Norm\riak\Location;
use Norm\riak\Member;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalendarService {
    public function createNew(Location $location, $name) {
        $calendar = new Calendar();
        $calendar->locationId = $location->id;
        $calendar->name = $name;
        $calendar->save();

        $location->calendarIds[] = $calendar->id;
        $location->save();

        return $calendar;
    }
}