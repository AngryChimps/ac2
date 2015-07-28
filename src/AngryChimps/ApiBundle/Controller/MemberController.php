<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\services\InfoService;
use AngryChimps\ApiBundle\Services\AuthService;
use AngryChimps\ApiBundle\Services\MemberService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Norm\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;

/**
 * Class MemberController
 * @package AngryChimps\ApiBundle\Controller
 *
 * @Route("/member")
 */
class MemberController extends AbstractRestController
{
    /** @var  MemberService */
    protected $memberService;

    /** @var  AuthService */
    private $authService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, MemberService $memberService, AuthService $authService,
                                InfoService $infoService)
    {
        parent::__construct($requestStack, $sessionService, $responseService, $memberService, $infoService);
        $this->memberService = $memberService;
        $this->authService = $authService;
    }

    public function indexGetAction($id)
    {
         return $this->getGetResponse('member', $id);
    }

    public function indexPostAction() {
        if($this->getAuthenticatedUser() !== null) {
            return $this->responseService->failure(400, ResponseService::AUTHENTICATED_MEMBER_ALREADY_IN_SESSION);
        }

        return $this->getPostResponse('member');
    }

    public function indexPatchAction($id) {
        return $this->getPatchResponse('member', $id);
    }

    public function indexDeleteAction($id) {
        return $this->getPatchResponse('member', $id);
    }
}
