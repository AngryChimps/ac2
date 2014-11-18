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
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $location = Location::getByPk($id);

        $company = Company::getByPk($location->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'LocationController.indexPutAction.2'
            );
            return $this->failure(400, $errors);
        }

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexGetAction.1'
            );
            return $this->failure(404, $errors);
        }

        $user = $this->getUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            return $this->success(array('location' => $location->getPrivateArray()));
        }
        else {
            return $this->success(array('location' => $location->getPublicArray()));
        }
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
        $street2 = $payload['street2'];
        $zip = $payload['zip'];
        $phone = $payload['phone'];

        $company = Company::getByPk($companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'CompanyController.indexPostAction.1'
            );
            return $this->failure(404, $errors);
        }

        $errors = array();
        if($location = $this->getLocationService()->createLocation($name, $street1, $street2, $zip, $phone, $company, $this->user, $errors) === false) {
            $errors = array(
                'human' => 'Error validating location fields',
                'code' => 'LocationController.indexPostAction.1'
            );
            return $this->failure(400, $errors);
        }

        return $this->success(array('company' => $location->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $location = Location::getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexPutAction.1'
            );
            return $this->failure(404, $errors);
        }

        $company = Company::getByPk($location->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'LocationController.indexPutAction.2'
            );
            return $this->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexPutAction.3'
            );
            return $this->failure(401, $errors);
        }

        $payload = $this->getPayload();
        $name = $payload['name'];
        $street1 = $payload['street1'];
        $street2 = $payload['street2'];
        $zip = $payload['zip'];
        $phone = $payload['phone'];

        $errors = array();
        if($company = $this->getLocationService()->updateLocation($location, $company, $name, $street1, $street2, $zip, $phone, $errors) === false) {
            $errors = array(
                'human' => 'Unable to validate location inputs',
                'code' => 'CompanyController.indexPutAction.4',
            );
            return $this->failure(400, $errors);
        }

        return $this->success(array('company' => $company));
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id)
    {
        $location = Location::getByPk($id);

        if($location === null) {
            $errors = array(
                'human' => 'Unable to find a location with that id',
                'code' => 'LocationController.indexDeleteAction.1'
            );
            return $this->failure(404, $errors);
        }

        $company = Company::getByPk($location->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that location',
                'code' => 'LocationController.indexDeleteAction.2'
            );
            return $this->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexDeleteAction.3'
            );
            return $this->failure(401, $errors);
        }

        $location->status = Location::DISABLED_STATUS;
        $location->save();

        return $this->success();
    }

}
