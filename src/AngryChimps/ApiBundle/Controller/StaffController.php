<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\services\InfoService;
use AngryChimps\ApiBundle\Services\StaffService;
use AngryChimps\GeoBundle\services\GeolocationService;
use Norm\Company;
use Norm\MemberCompany;
use Norm\Staff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;

class StaffController extends AbstractRestController
{
    /** @var  StaffService */
    protected $staffService;

    /** @var  InfoService */
    protected $infoService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, StaffService $staffService,
                                InfoService $infoService) {
        parent::__construct($requestStack, $sessionService, $responseService, $staffService, $infoService);

        $this->staffService = $staffService;
    }

    public function indexGetAction($id)
    {
        return $this->getGetResponse('staff', $id);
    }

    public function indexPostAction()
    {
        return $this->getPostResponse('staff',
            [
                'created_by' => $this->getAuthenticatedUser()->getId(),
                'status' => Staff::PARTIALLY_CONFIGURED_STATUS,
            ]
        );
    }

    public function indexPatchAction($id)
    {
        return $this->getPatchResponse('staff', $id);
    }

    public function indexDeleteAction($id)
    {
        return $this->getDeleteResponse('staff', $id);
    }
}
