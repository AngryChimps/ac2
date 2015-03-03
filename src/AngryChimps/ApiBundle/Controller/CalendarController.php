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
use AngryChimps\ApiBundle\Services\ServiceService;

class CalendarController extends AbstractController
{
    /** @var CalendarService  */
    protected $calendarService;

    /** @var CompanyService */
    protected $companyService;

    /** @var  LocationService */
    protected $locationService;

    /** @var ServiceService */
    protected $serviceService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CalendarService $calendarService,
                                CompanyService $companyService, LocationService $locationService,
                                ServiceService $serviceService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->calendarService = $calendarService;
        $this->companyService = $companyService;
        $this->locationService = $locationService;
        $this->serviceService = $serviceService;
    }

    public function indexGetAction($calendarId, $serviceId = null) {
        $calendar = $this->calendarService->getCalendar($calendarId);

        if($calendar === null) {
            $error = array('code' => 'Api.CalendarController.indexGetAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $company = $this->companyService->getByPk($calendar->companyId);

        if($serviceId === null) {
            if ($this->isAuthorizedSelf($company->administerMemberIds)) {
                $data = $this->calendarService->getData($calendar, true);
            }
            else {
                $data = $this->calendarService->getData($calendar);
            }
        }
        else {
            $service = $this->serviceService->getService($serviceId);

            if($service === null) {
                $error = array('code' => 'Api.CalendarController.indexGetAction.2',
                    'human' => 'Unable to find the specified service');
                return $this->responseService->failure(400, $error);
            }

            $data = $this->calendarService->getAvailableTimeWindows($calendar->availabilities,
                $service->minsForService, $service->minsNotice);
        }

        return $this->responseService->success($data);
    }

    public function indexPostAction() {
        $payload = $this->getPayload();

        $location = $this->locationService->getByPk($payload['location_id']);

        if($location === null) {
            $error = array('code' => 'Api.CalendarController.indexPostAction.1',
                'human' => 'Unable to find the specified location');
            return $this->responseService->failure(400, $error);
        }

        $cal = $this->calendarService->createNew($location, $payload['name']);

        return $this->responseService->success(['calendar' => ['id' => $cal->id]]);
    }

    public function indexPutAction($calendarId) {
        $payload = $this->getPayload();

        $changes = [];
        if(isset($payload['name'])) {
            $changes['name'] = $payload['name'];
        }

        $calendar = $this->calendarService->getByPk($calendarId);

        if($calendar === null) {
            $error = array('code' => 'Api.CalendarController.indexPutAction.1',
                'human' => 'Unable to find the specified calendar');
            return $this->responseService->failure(404, $error);
        }

        $valid = $this->calendarService->update($calendar, $changes);

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
