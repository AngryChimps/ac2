<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\CalendarService;

class BookingController extends AbstractController
{
    /** @var CalendarService  */
    protected $calendarService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CalendarService $calendarService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->calendarService = $calendarService;
    }

}
