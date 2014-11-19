<?php

namespace AngryChimps\ApiBundle\Controller;

use Norm\riak\Service;
use Norm\riak\Company;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ServiceController extends AbstractController
{
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $service = Service::getByPk($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service with that id',
                'code' => 'Api.ServiceController.indexGetAction.1'
            );
            return $this->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that service',
                'code' => 'Api.ServiceController.indexGetAction.2'
            );
            return $this->failure(400, $errors);
        }

        $user = $this->getUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            return $this->success(array('service' => $service->getPrivateArray()));
        }
        else {
            return $this->success(array('service' => $service->getPublicArray()));
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
        $discountedPrice = $payload['discounted_price'];
        $originalPrice = $payload['original_price'];
        $minsForService = $payload['mins_for_service'];
        $minsNotice = $payload['mins_notice'];
        $category = $payload['category'];

        $company = Company::getByPk($companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.ServiceController.indexPostAction.1'
            );
            return $this->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexPostAction.2'
            );
            return $this->failure(401, $errors);
        }

        $errors = array();
        if($service = $this->getServiceService()->createService($name, $companyId, $discountedPrice,
                $originalPrice, $minsForService, $minsNotice, $category, $errors) === false) {
            $errors = array(
                'human' => 'Error validating service fields',
                'code' => 'Api.ServiceController.indexPostAction.3',
                'debug' => $errors,
            );
            return $this->failure(400, $errors);
        }

        return $this->success(array('service' => $service->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $payload = $this->getPayload();
        $id = $payload['id'];
        $name = $payload['name'];
        $discountedPrice = $payload['discounted_price'];
        $originalPrice = $payload['original_price'];
        $minsForService = $payload['mins_for_service'];
        $minsNotice = $payload['mins_notice'];
        $category = $payload['category'];

        $service = Service::getByPk($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service  with that id',
                'code' => 'Api.ServiceController.indexPutAction.1'
            );
            return $this->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.ServiceController.indexPutAction.2'
            );
            return $this->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexPutAction.3'
            );
            return $this->failure(401, $errors);
        }

        $errors = array();
        if($company = $this->getServiceService()->updateService($service, $name, $discountedPrice,
            $originalPrice, $minsForService, $minsNotice, $category,
            $errors) === false) {
            $errors = array(
                'human' => 'Unable to validate location inputs',
                'code' => 'Api.ServiceController.indexPutAction.4',
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
        $service = Service::getByPk($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service  with that id',
                'code' => 'Api.ServiceController.indexDeleteAction.1'
            );
            return $this->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.ServiceController.indexDeleteAction.2'
            );
            return $this->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexDeleteAction.3'
            );
            return $this->failure(401, $errors);
        }

        $service->status = Service::DISABLED_STATUS;
        $service->save();

        return $this->success();
    }

}
