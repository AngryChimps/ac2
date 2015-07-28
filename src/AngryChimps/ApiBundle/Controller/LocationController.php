<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\Services\InfoService;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\SessionService;
use Norm\Company;
use Norm\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocationController extends AbstractRestController
{
    /** @var  LocationService */
    protected $locationService;

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, LocationService $locationService,
                                CompanyService $companyService, InfoService $infoService)
    {
        $this->locationService = $locationService;
        $this->companyService = $companyService;
        parent::__construct($requestStack, $sessionService, $responseService, $locationService, $infoService);
    }


    public function indexGetAction($id)
    {
        //Check to see if the token/member_id is valid
        if($debug = $this->sessionService->checkToken()) {
            return $this->responseService->failure(400, ResponseService::INVALID_SESSION_INFORMATION, null, $debug);
        }

        /** @var Location $location */
        $location = $this->locationService->get('location', $id);

        if($location === null) {
            return $this->responseService->failure(404, ResponseService::ERROR_404);
        }

        //Get location data
        if($this->locationService->isOwner($location, $this->getAuthenticatedUser())) {
            $locationData = ['location' => $this->locationService->getApiPrivateArray($location)];
        }
        else {
            $locationData = ['location' => $this->locationService->getApiPublicArray($location)];
        }

        //Get company data
        /** @var Company $company */
        $company = $this->companyService->get('company', $location->getCompanyId());
        $companyData = ['company' => $this->companyService->getApiPublicArray($company)];

        //Get staff data
        $staffData = ['staff' => []];

        //Get review data
        $reviewData = ['reviews' => []];

        $allData = array_merge($locationData, $companyData, $staffData, $reviewData);

        return $this->responseService->success($allData);
    }

    public function indexPostAction()
    {
        //Make sure the member owns the company
        $company_id = $this->getPayload()['company_id'];
        $company = $this->companyService->get('company', $company_id);
        if(!$this->companyService->isOwner($company, $this->getAuthenticatedUser())) {
            return $this->responseService->failure(403, ResponseService::USER_NOT_AUTHORIZED);
        }

        return $this->getPostResponse('location',
            [
                'created_by' => $this->getAuthenticatedUser()->getId(),
                'status' => Location::PARTIALLY_CONFIGURED_STATUS,
            ]
        );
    }

    public function indexPatchAction($id)
    {
        return $this->getPatchResponse('location', $id);
    }

    public function indexDeleteAction($id)
    {
        return $this->getDeleteResponse('location', $id);
    }


}
