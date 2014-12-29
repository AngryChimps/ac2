<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\SessionService;
use Norm\riak\Company;
use Norm\riak\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LocationController
 * @package AngryChimps\ApiBundle\Controller
 *
 * @Route("/location")
 */
class LocationController extends AbstractController
{
    /** @var  LocationService */
    protected $locationService;

    /** @var CompanyService  */
    protected $companyService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, LocationService $locationService,
                                CompanyService $companyService)
    {
        $this->locationService = $locationService;
        $this->companyService = $companyService;
        parent::__construct($requestStack, $sessionService, $responseService); // TODO: Change the autogenerated stub
    }


    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $location = $this->locationService->getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'Api.LocationController.indexGetAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($location->companyId);

        //Should never happen
        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'Api.LocationController.indexGetAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        $arr = [];
        $arr['id'] = $location->id;
        $arr['name'] = $location->name;
        $arr['address'] = (array) $location->address;
        $arr['phone'] = $location->address->phone;
        $arr['is_mobile'] = $location->isMobile;

        $user = $this->getAuthenticatedUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            $arr['calendarIds'] = $location->calendarIds;
        }
        return $this->responseService->success(array('location' => $arr));
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction()
    {
        $payload = $this->getPayload();
        $name = $payload['name'];
        $companyId = $payload['company_id'];
        $street1 = $payload['street1'];
        if(isset($payload['street2'])) {
            $street2 = $payload['street2'];
        }
        else {
            $street2 = null;
        }
        $zip = $payload['zip'];
        $phone = $payload['phone'];
        $isMobile = $payload['is_mobile'];

        $company = $this->companyService->getByPk($companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.LocationController.indexPostAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $errors = array();
        $location = $this->locationService->createLocation($name, $street1, $street2, $zip, $phone, $company, $isMobile, $errors);
        if($location === false) {
            $errors = array(
                'human' => 'Error validating location fields',
                'code' => 'Api.LocationController.indexPostAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        return $this->responseService->success(array('location' => array('id' => $location->id)));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $location = $this->locationService->getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'Api.LocationController.indexPutAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($location->companyId);

        //This should never happen
        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'Api.LocationController.indexPutAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.LocationController.indexPutAction.3'
            );
            return $this->responseService->failure(401, $errors);
        }

        $payload = $this->getPayload();
        $name = $payload['name'];
        $street1 = $payload['street1'];
        $street2 = $payload['street2'];
        $zip = $payload['zip'];
        $phone = $payload['phone'];
        $isMobile = $payload['is_mobile'];

        $errors = array();
        $location = $this->locationService->updateLocation($location, $name, $street1, $street2, $zip, $phone, $isMobile, $errors);
        if($location === false) {
            $errors = array(
                'human' => 'Unable to validate location inputs',
                'code' => 'Api.LocationController.indexPutAction.4',
            );
            return $this->responseService->failure(400, $errors);
        }

        return $this->responseService->success();
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id)
    {
        $location = $this->locationService->getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'Api.LocationController.indexDeleteAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($location->companyId);

        //Should never happen with valid data
        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'Api.LocationController.indexDeleteAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.LocationController.indexDeleteAction.3'
            );
            return $this->responseService->failure(401, $errors);
        }

        $this->locationService->markLocationDeleted($location);

        return $this->responseService->success();
    }

}
