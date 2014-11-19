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
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexGetAction.1'
            );
            return $this->failure(404, $errors);
        }

        $user = $this->getUser();
        if($user !== null && in_array($user->id, $company->administerMemberIds)) {
            return $this->success(array('company' => $company->getPrivateArray()));
        }
        else {
            return $this->success(array('company' => $company->getPublicArray()));
        }
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction()
    {
        $payload = $this->getPayload();

        $errors = array();
        $user = $this->getUser();
        if($company = $this->getCompanyService()->createCompany($payload['name'], $user, $errors) === false) {
            $errors = array(
                'human' => 'Error validating company fields',
                'code' => 'Api.CompanyController.indexPostAction.1'
            );
            return $this->failure(400, $errors);
        }

        return $this->success(array('company' => $company->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id)
    {
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexPutAction.1'
            );
            return $this->failure(404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.CompanyController.indexPutAction.2'
            );
            return $this->failure(401, $errors);
        }

        $payload = $this->getPayload();
        $errors = array();
        $user = $this->getUser();
        if($company = $this->getCompanyService()->createCompany($payload['name'], $user, $errors) === false) {
            $errors = array(
                'human' => 'Unable to validate company inputs',
                'code' => 'Api.CompanyController.indexPutAction.3',
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
        $company = Company::getByPk($id);

        if($company === null) {
            $errors = array(
                'human' => 'Unable to find a company with that id',
                'code' => 'Api.CompanyController.indexDeleteAction.1'
            );
            return $this->failure(404, $errors);
        }

        if(!$this->isAuthorizedSelf($company->administerMemberIds)) {
            $errors = array(
                'human' => 'This user is not authorized to perform this action',
                'code' => 'Api.CompanyController.indexDeleteAction.2'
            );
            return $this->failure(401, $errors);
        }

        $company->status = Company::DISABLED_STATUS;
        $company->save();

        return $this->success();
    }

}
