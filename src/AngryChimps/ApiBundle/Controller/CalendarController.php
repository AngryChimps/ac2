<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CalendarService;
use AngryChimps\NormBundle\realms\Norm\riak\services\NormRiakService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class CalendarController extends AbstractController
{
    /** @var CalendarService  */
    protected $calendarService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CalendarService $calendarService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->calendarService = $calendarService;
    }

    public function indexGetAction($calendarId) {
        $calendar = $this->calendarService->getCalendar($calendarId);

        if($calendar === null) {
            $error = array('code' => 'Api.CalendarController.indexGetAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $data = $this->calendarService->getData($calendar);

        return $this->responseService->success(array('payload' => $data));
    }

    public function indexDeleteAction($calendarId) {
        $calendar = $this->calendarService->getCalendar($calendarId);

        if($calendar === null) {
            $error = array('code' => 'Api.CalendarController.indexDeleteAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $this->calendarService->markDeleted($calendar);

        return $this->responseService->success();
    }
}
