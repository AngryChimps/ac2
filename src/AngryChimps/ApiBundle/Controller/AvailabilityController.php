<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Availability;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\CalendarService;
use AngryChimps\ApiBundle\Services\ResponseService;

class AvailabilityController extends AbstractController
{
    /** @var CalendarService  */
    protected $calendarService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CalendarService $calendarService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->calendarService = $calendarService;
    }

    public function indexPostAction() {
        $payload = $this->getPayload();

        $calendar = $this->calendarService->getCalendar($payload['calendar_id']);

        if($calendar === null) {
            $error = array('code' => 'Api.AvailabilityController.indexPostAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $availability = new Availability();
        $availability->start = new \DateTime($payload['start']);
        $availability->end = new \DateTime($payload['end']);

        $success = $this->calendarService->addAvailability($calendar, $availability);

        if($success === false) {
            $error = array('code' => 'Api.AvailabilityController.indexPostAction.2',
                'human' => 'Unable to create the availability due to a conflicting booking');
            return $this->responseService->failure(404, $error);
        }

        return $this->responseService->success();
    }
}
