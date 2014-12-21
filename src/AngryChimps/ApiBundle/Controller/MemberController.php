<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\AuthService;
use AngryChimps\ApiBundle\Services\MemberService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Norm\riak\Member;
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
class MemberController extends AbstractController
{
    /** @var  MemberService */
    protected $memberService;

    /** @var  AuthService */
    private $authService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, MemberService $memberService, AuthService $authService)
    {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->memberService = $memberService;
        $this->authService = $authService;
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id)
    {
        $member = $this->memberService->getMember($id);

        if($member === null) {
            $errors = array(
                'human' => 'Unable to find the requested member',
                'code' => 'Api.MemberController.indexGetAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $user = $this->getAuthenticatedUser();

        $arr = [];
        $arr['id'] = $member->id;
        $arr['name'] = $member->name;
        $arr['photo'] = $member->photo;

        if($user !== null && $member->id === $user->id) {
            $arr['email'] = $member->email;
        }

        $data = array('member' => $arr);

        return $this->responseService->success($data);
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id) {
        if(!$this->isAuthorizedSelf($id)) {
            $errors = array(
                'human' => 'Only the authenticated user may delete his or her object',
                'code' => 'Api.MemberController.indexDeleteAction.1',
            );
            return $this->responseService->failure(401, $errors);
        }

        $member =$this->memberService->getMember($id);

        if($member === null) {
            $error = array(
                'human' => 'Unable to find the requested member',
                'code' => 'Api.MemberController.indexDeleteAction.2'
            );
            return $this->responseService->failure(404, $error);
        }

        $this->memberService->markMemberDeleted($member);

        return $this->responseService->success();
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id) {
        $user = $this->getAuthenticatedUser();
        $payload = $this->getPayload();

        if(!$this->isAuthorizedSelf($id)) {
            $errors = array(
                'human' => 'This action can only be performed by the owner of the object',
                'code' => 'Api.MemberController.indexPutAction.1',
            );
            return $this->responseService->failure(401, $errors);
        }

        $changes = [];
        if(isset($payload['name'])) {
            $changes['name'] = $payload['name'];
        }
        if(isset($payload['email'])) {
            $changes['email'] = $payload['email'];
        }

        $errors = array();
        $valid = $this->memberService->update($user, $changes, $errors);
        if(!$valid) {
            $errors = array(
                'human' => 'Unable to validate member inputs',
                'code' => 'Api.MemberController.indexPutAction.2',
            );
            return $this->responseService->failure(400, $errors);
        }

        return $this->responseService->success();
    }
}
