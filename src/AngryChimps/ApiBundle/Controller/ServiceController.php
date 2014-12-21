<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyService;
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

    /** @var  CompanyService */
    protected $companyService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, ServiceService $serviceService,
                                CompanyService $companyService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->serviceService = $serviceService;
        $this->companyService = $companyService;
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $service = $this->serviceService->getService($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service with that id',
                'code' => 'Api.ServiceController.indexGetAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($service->companyId);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company which corresponds to that service',
                'code' => 'Api.ServiceController.indexGetAction.2'
            );
            return $this->responseService->failure(400, $errors);
        }

        $arr = [];
        $arr['id'] = $service->id;
        $arr['name'] = $service->name;
        $arr['description'] = $service->description;
        $arr['discounted_price'] = $service->discountedPrice;
        $arr['original_price'] = $service->originalPrice;
        $arr['mins_for_service'] = $service->minsForService;

        $user = $this->getAuthenticatedUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            $arr['mins_notice'] = $service->minsNotice;
        }
        return $this->responseService->success(array('service' => $arr));
=    }

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

        $company = $this->companyService->getByPk($companyId);

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

        return $this->responseService->success(array('service' => array('id' => $service->id)));
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

        $service = $this->serviceService->getService($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service  with that id',
                'code' => 'Api.ServiceController.indexPutAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($service->companyId);

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
        $service = $this->serviceService->getService($id);

        if($service === null) {
            $errors = array(
                'human' => 'Unable to find a service  with that id',
                'code' => 'Api.ServiceController.indexDeleteAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $company = $this->companyService->getByPk($service->companyId);

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

        $this->serviceService->markServiceDeleted($service);

        return $this->responseService->success();
    }

}
