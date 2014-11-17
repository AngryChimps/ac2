<?php

namespace AngryChimps\ApiBundle\Controller;

use Norm\riak\Company;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

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

    public function __construct() {
        $this->companyService = $this->get('angry_chimps_api.company');
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id, Request $request)
    {
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'CompanyController.indexGetAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        if($this->user !== null && in_array($this->user->id, $company->administerMemberIds)) {
            return $this->success($request, array('company' => $company->getPrivateArray()));
        }
        else {
            return $this->success($request, array('company' => $company->getPublicArray()));
        }
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction(Request $request)
    {
        $payload = $this->getPayload();

        $errors = array();
        if($company = $this->companyService->createCompany($payload['name'], $this->user, $errors) === false) {
            $errors = array(
                'human' => 'Error validating company fields',
                'code' => 'CompanyController.indexPostAction.1'
            );
            return $this->failure($request, 400, $errors);
        }

        return $this->success($request, array('company' => $company->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id, Request $request)
    {
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'CompanyController.indexPutAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexPutAction.2'
            );
            return $this->failure($request, 401, $errors);
        }

        $payload = $this->getPayload();
        $errors = array();
        if($company = $this->companyService->createCompany($payload['name'], $this->user, $errors) === false) {
            $errors = array(
                'human' => 'Unable to validate company inputs',
                'code' => 'CompanyController.indexPutAction.3',
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
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'CompanyController.indexDeleteAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'CompanyController.indexDeleteAction.2'
            );
            return $this->failure($request, 401, $errors);
        }

        $company->status = Company::DISABLED_STATUS;
        $company->save();

        return $this->success($request);
    }

}
