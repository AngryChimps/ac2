<?php

namespace AngryChimps\ApiBundle\Controller;

use Norm\riak\Company;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;

/**
 * Class CompanyController
 * @package AngryChimps\ApiBundle\Controller
 *
 * @Route("/company")
 */
class CompanyController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\CompanyService */
    protected $companyService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CompanyService $companyService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->companyService = $companyService;
    }

    public function indexGetAction($id)
    {
        $company = $this->companyService->getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexGetAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $arr = [];
        $arr['id'] = $company->id;
        $arr['name'] = $company->name;
        $arr['description'] = $company->description;

        return $this->responseService->success(array('company' => $arr));
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction()
    {
        $payload = $this->getPayload();

        $errors = array();
        $user = $this->getAuthenticatedUser();
        $company = $this->companyService->createCompany($payload['name'], $user, $errors);
        if($company === false) {
            $errors = array(
                'human' => 'Error validating company fields',
                'code' => 'Api.CompanyController.indexPostAction.1'
            );
            return $this->responseService->failure(400, $errors);
        }

        return $this->responseService->success(array('company' => array('id' => $company->id)));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $company = $this->companyService->getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexPutAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.CompanyController.indexPutAction.2'
            );
            return $this->responseService->failure(401, $errors);
        }

        $payload = $this->getPayload();
        $errors = array();

        $valid = $this->companyService->updateCompany($company, $payload['name'], $errors);
        if(!$valid) {
            $errors = array(
                'human' => 'Unable to validate company inputs',
                'code' => 'Api.CompanyController.indexPutAction.3',
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
        $company = $this->companyService->getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexDeleteAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.CompanyController.indexDeleteAction.2'
            );
            return $this->responseService->failure(403, $errors);
        }

        $this->companyService->markCompanyDeleted($company);

        return $this->responseService->success();
    }
}
