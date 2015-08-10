<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\Services\InfoService;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\ReviewService;
use AngryChimps\ApiBundle\Services\SessionService;
use AngryChimps\ApiBundle\Services\StaffService;
use Norm\Company;
use Norm\Location;
use Norm\Review;
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

    /** @var StaffService */
    protected $staffService;

    /** @var ReviewService */
    protected $reviewService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, LocationService $locationService,
                                CompanyService $companyService, InfoService $infoService,
                                StaffService $staffService, ReviewService $reviewService)
    {
        $this->locationService = $locationService;
        $this->companyService = $companyService;
        $this->staffService = $staffService;
        $this->reviewService = $reviewService;

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
            $allData = ['location' => $this->locationService->getApiPrivateArray($location)];
        }
        else {
            $allData = ['location' => $this->locationService->getApiPublicArray($location)];
        }

        //Add in walk-in and emergency status
        $allData['location']['walk_ins'] = in_array(2500, $location->getServices());
        $allData['location']['emergencies'] = in_array(700, $location->getServices());

        //Get company data
        if($this->request->get('company') !== null) {
            /** @var Company $company */
            $company = $this->companyService->get('company', $location->getCompanyId());
            $companyData = ['company' => $this->companyService->getApiPublicArray($company)];
            $allData = array_merge($allData, $companyData);
        }

        //Get staff data
        if($this->request->get('staff_count') !== null) {
            $totalStaffCount = $this->locationService->getStaffCount($location->getId());
            $staffArray = $this->locationService->getStaff($location->getId(), (int) $this->request->get('staff_count'));
            $staffData = ['staff' =>
                                [
                                    'count' => $totalStaffCount,
                                    'results' => $this->staffService->getApiPublicArray($staffArray)
                                ]
                         ];
            $allData = array_merge($allData, $staffData);
        }

        //Get review data
        if($this->request->get('review_count') !== null) {
            $totalReviewCount = $this->locationService->getReviewCount($location->getId());
            $reviewArray = $this->locationService->getReviews($location->getId(), (int) $this->request->get('review_count'));
            $reviewData = ['reviews' =>
                [
                    'count' => $totalReviewCount,
                    'results' => $this->reviewService->getApiPublicArray($reviewArray)
                ]
            ];
            $allData = array_merge($allData, $reviewData);
        }

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
