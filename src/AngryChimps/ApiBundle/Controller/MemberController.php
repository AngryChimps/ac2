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
        $member = Member::getByPk($id);

        if($member === null) {
            $errors = array(
                'human' => 'Unable to find the requested member',
                'code' => 'Api.MemberController.indexGetAction.1'
            );
            return $this->responseService->failure(404, $errors);
        }

        $user = $this->getAuthenticatedUser();
        if($user !== null && $member->id === $user->id) {
            $memberInfo = $member->getPrivateArray();
        }
        else {
            $memberInfo = $member->getPublicArray();
        }
        $data = array('member' => $memberInfo);

        return $this->responseService->success($data);
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id) {
        if($this->isAuthorizedSelf($id)) {
            $errors = array(
                'human' => 'You must be a super_user to do this',
                'code' => 'Api.MemberController.indexDeleteAction.1',
            );
            return $this->responseService->failure(401, $errors);
        }

        $member = Member::getByPkEnabled($id);

        if($member === null) {
            $error = array(
                'human' => 'Unable to find the requested member',
                'code' => 'Api.MemberController.indexDeleteAction.2'
            );
            return $this->responseService->failure(404, $error);
        }

        $member->status = Member::DELETED_STATUS;
        $member->save();

        return $this->responseService->success();
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id) {
        $user = $this->getUser();

        if($this->isAuthorizedSelf($id)) {
            $errors = array(
                'human' => 'This action can only be performed by the owner of the object',
                'code' => 'Api.MemberController.indexPutAction.1',
            );
            return $this->responseService->failure(401, $errors);
        }

        $payload = $this->getPayload();

        if(isset($payload['name'])) {
            $user->name = $payload['name'];
        }
        if(isset($payload['email'])) {
            $user->email = $payload['email'];
        }
        if(isset($payload['dob'])) {
            $user->dob = new \DateTime($payload['dob']);
        }
        $user->save();

        return $this->responseService->success($user->getPrivateArray());
    }
}
