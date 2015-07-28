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
        $isOwner = $this->getAuthenticatedUser() && ($this->getAuthenticatedUser()->getId() == $id);

        return $this->getGetResponse('member', $id, $isOwner);
    }

    public function indexPostAction() {
        if($this->getAuthenticatedUser() !== null) {
            return $this->responseService->failure(400, ResponseService::AUTHENTICATED_MEMBER_ALREADY_IN_SESSION);
        }

        return $this->getPostResponse('member');
    }

    public function indexPatchAction($id) {
        $user = $this->getAuthenticatedUser();

        if($user === null) {
            return $this->responseService->failure(401, ResponseService::USER_NOT_AUTHENTICATED);
        }

        $isOwner = ($user->getId() == $id);

        return $this->getPatchResponse('member', $id, $isOwner);
    }

    public function indexDeleteAction($id) {
        $user = $this->getAuthenticatedUser();

        if($user === null) {
            return $this->responseService->failure(401, ResponseService::USER_NOT_AUTHENTICATED);
        }

        $isOwner = ($user->getId() != $id);

        return $this->getPatchResponse('member', $id, $isOwner);
    }
}
