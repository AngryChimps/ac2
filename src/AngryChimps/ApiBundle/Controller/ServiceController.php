<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\ServiceService;
use Norm\riak\Service;
use Norm\riak\Company;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;

/**
 * Class ServiceController
 *
 * @Route("/service")
 */
class ServiceController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\ServiceService */
    protected $serviceService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, ServiceService $serviceService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->serviceService = $serviceService;
    }

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
            return $this->responseService->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that service',
                'code' => 'Api.ServiceController.indexGetAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        $user = $this->getAuthenticatedUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            return $this->responseService->success(array('service' => $service->getPrivateArray()));
        }
        else {
            return $this->responseService->success(array('service' => $service->getPublicArray()));
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
            return $this->responseService->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexPostAction.2'
            );
            return $this->responseService->failure(401, $errors);
        }

        $errors = array();
        $service = $this->serviceService->createService($name, $companyId, $discountedPrice,
            $originalPrice, $minsForService, $minsNotice, $category, $errors);
        if($service === false) {
            $errors = array(
                'human' => 'Error validating service fields',
                'code' => 'Api.ServiceController.indexPostAction.3',
                'debug' => $errors,
            );
            return $this->responseService->failure(400, $errors);
        }

        return $this->responseService->success(array('service' => $service->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $payload = $this->getPayload();
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
            return $this->responseService->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.ServiceController.indexPutAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexPutAction.3'
            );
            return $this->responseService->failure(401, $errors);
        }

        $errors = array();
        $company = $this->serviceService->updateService($service, $name, $discountedPrice,
            $originalPrice, $minsForService, $minsNotice, $category,
            $errors);
        if($company === false) {
            $errors = array(
                'human' => 'Unable to validate location inputs',
                'code' => 'Api.ServiceController.indexPutAction.4',
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
        $service = Service::getByPk($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service  with that id',
                'code' => 'Api.ServiceController.indexDeleteAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = Company::getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company  with that id',
                'code' => 'Api.ServiceController.indexDeleteAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.ServiceController.indexDeleteAction.3'
            );
            return $this->responseService->failure(401, $errors);
        }

        $service->status = Service::DISABLED_STATUS;
        $service->save();

        return $this->responseService->success();
    }

}
