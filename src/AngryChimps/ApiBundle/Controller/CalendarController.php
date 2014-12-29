<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CalendarService;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
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

    /** @var CompanyService */
    protected $companyService;

    /** @var  LocationService */
    protected $locationService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CalendarService $calendarService,
                                CompanyService $companyService, LocationService $locationService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->calendarService = $calendarService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
    }

    public function indexGetAction($calendarId) {
        $calendar = $this->calendarService->getCalendar($calendarId);

        if($calendar === null) {
            $error = array('code' => 'Api.CalendarController.indexGetAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $company = $this->companyService->getByPk($calendar->companyId);

        if($this->isAuthorizedSelf($company->administerMemberIds)) {
            $data = $this->calendarService->getData($calendar, true);
        }
        else {
            $data = $this->calendarService->getData($calendar);
        }

        return $this->responseService->success(array('payload' => $data));
    }

    public function indexPostAction() {
        $payload = $this->getPayload();

        $location = $this->locationService->getByPk($payload['location_id']);

        if($location === null) {
            $error = array('code' => 'Api.CalendarController.indexPostAction.1',
                'human' => 'Unable to find the specified location');
            return $this->responseService->failure(400, $error);
        }

        $this->calendarService->createNew($location, $payload['name']);

        return $this->responseService->success();
    }

    public function indexPutAction() {
        $payload = $this->getPayload();

        $changes = [];
        if(isset($payload['name'])) {
            $changes['name'] = $payload['name'];
        }

        $location = $this->locationService->getByPk($payload['location_id']);

        if($location === null) {
            $error = array('code' => 'Api.CalendarController.indexPutAction.1',
                'human' => 'Unable to find the specified location');
            return $this->responseService->failure(400, $error);
        }

        $valid = $this->calendarService->update($location, $changes);

        if($valid) {
            return $this->responseService->success();
        }
        else {
            $error = array('code' => 'Api.CalendarController.indexPutAction.2',
                'human' => 'Unable to update the specified fields');
            return $this->responseService->failure(400, $error);
        }
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
