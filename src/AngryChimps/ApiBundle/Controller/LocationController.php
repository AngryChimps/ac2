<?php

namespace AngryChimps\ApiBundle\Controller;

use Norm\riak\Company;
use Norm\riak\Location;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocationController
 * @package AngryChimps\ApiBundle\Controller
 *
 * @Route("/location")
 */
class LocationController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\LocationService */
    protected $locationService;

    public function __construct() {
        $this->locationService = $this->get('angry_chimps_api.location');
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id, Request $request)
    {
        $location = Location::getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexGetAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        if($this->user !== null && in_array($this->user->id, $location->administerMemberIds)) {
            return $this->success($request, array('location' => $location->getPrivateArray()));
        }
        else {
            return $this->success($request, array('location' => $location->getPublicArray()));
        }
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction(Request $request)
    {
        $payload = $this->getPayload();
        $name = $payload['name'];
        $companyId = $payload['company_id'];
        $street1 = $payload['street1'];
        $street2 = $payload['street2'];
        $zip = $payload['zip'];
        $phone = $payload['phone'];

        $company = Company::getByPk($companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find acompany  with that id',
                'code' => 'CompanyController.indexPutAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        $errors = array();
        if($location = $this->locationService->createLocation($name, $street1, $street2, $zip, $phone, $company, $this->user, $errors) === false) {
            $errors = array(
                'human' => 'Error validating location fields',
                'code' => 'LocationController.indexPostAction.1'
            );
            return $this->failure($request, 400, $errors);
        }

        return $this->success($request, array('company' => $location->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id, Request $request)
    {
        $location = Location::getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexPutAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        $company = Company::getByPk($location->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'LocationController.indexPutAction.2'
            );
            return $this->failure($request, 400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexPutAction.3'
            );
            return $this->failure($request, 401, $errors);
        }

        $payload = $this->getPayload();
        $name = $payload['name'];
        $street1 = $payload['street1'];
        $street2 = $payload['street2'];
        $zip = $payload['zip'];
        $phone = $payload['phone'];

        $errors = array();
        if($company = $this->locationService->updateLocation($location, $name, $street1, $street2, $zip, $phone, $errors) === false) {
            $errors = array(
                'human' => 'Unable to validate location inputs',
                'code' => 'CompanyController.indexPutAction.4',
            );
            return $this->failure($request, 400, $errors);
        }

        return $this->success($request, array('company' => $company));
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id, Request $request)
    {
        $location = Location::getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexDeleteAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        $company = Company::getByPk($location->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'LocationController.indexPutAction.2'
            );
            return $this->failure($request, 400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexPutAction.3'
            );
            return $this->failure($request, 401, $errors);
        }

        $location->status = Location::DISABLED_STATUS;
        $location->save();

        return $this->success($request);
    }

}
