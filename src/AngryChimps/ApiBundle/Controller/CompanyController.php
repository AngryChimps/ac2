<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\services\InfoService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\MemberCompany;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;

class CompanyController extends AbstractRestController
{
    /** @var  \AngryChimps\ApiBundle\Services\CompanyService */
    protected $companyService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CompanyService $companyService,
                                InfoService $infoService) {
        parent::__construct($requestStack, $sessionService, $responseService, $companyService, $infoService);
        $this->companyService = $companyService;
    }

    public function indexGetAction($id)
    {
        return $this->getGetResponse('company', $id);
    }

    public function indexPostAction()
    {
        return $this->getPostResponse('company',
            [
                'created_by' => $this->getAuthenticatedUser()->getId(),
                'status' => Company::PARTIALLY_CONFIGURED_STATUS,
            ]
        );
    }

    public function indexPatchAction($id)
    {
        return $this->getPatchResponse('company', $id);
    }

    public function indexDeleteAction($id)
    {
        return $this->getDeleteResponse('company', $id);
    }
}
