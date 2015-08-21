<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\SampleBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationController extends AbstractController
{
    /** @var LocationService */
    protected $locationService;

    public function __construct(RequestStack $requestStack, ResponseService $responseService,
                                LocationService $locationService) {
        parent::__construct($requestStack, $responseService);

        $this->locationService = $locationService;
    }

    public function indexGetAllAction($region, $companyShortName) {
        $shortNames = $this->locationService->getAllShortNames($region, $companyShortName);
        return $this->responseService->success(['location_short_names'=> $shortNames]);
    }

    public function indexPostAction($region, $companyShortName, $locationShortName) {
        $payload = $this->getPayload();
        $this->locationService->post($region, $companyShortName, $payload, $locationShortName);

        return $this->responseService->success();
    }

    public function indexGetAction($region, $companyShortName, $locationShortName) {
        $data = $this->locationService->get($region, $companyShortName, $locationShortName);

        return $this->responseService->success(['company' => $data]);
    }

    public function indexDeleteAction($region, $companyShortName, $locationShortName) {
        $this->locationService->delete($region, $companyShortName, $locationShortName);

        return $this->responseService->success();
    }

    public function indexPutAction($region, $companyShortName, $locationShortName) {
        $payload = $this->getPayload();
        $this->locationService->put($region, $companyShortName, $locationShortName, $payload);

        return $this->responseService->success();
    }
}
