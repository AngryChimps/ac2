<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\AuthService;
use Norm\riak\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MemberController
 * @package AngryChimps\ApiBundle\Controller
 *
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    /** @var \AngryChimps\ApiBundle\Services\MemberService $memberService */
    protected $memberService;

    public function __construct() {
        $this->memberService = $this->get('angry_chimps_api.member');
    }

    /**
     * @Route("/{id}")
     * @Method({"GET"})
     */
    public function indexGetAction($id, Request $request)
    {
        $member = Member::getByPk($id);

        if($member === null) {
            $errors = array(
                'human' => 'Unable to find the requested member',
                'code' => 'MemberController.indexGetAction.1'
            );
            return $this->failure($request, 404, $errors);
        }

        if($this->user !== null && $member->id === $this->user->id) {
            $memberInfo = $member->getPrivateArray();
        }
        else {
            $memberInfo = $member->getPublicArray();
        }
        $data = array('member' => $memberInfo);

        return $this->success($request, $data);
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction(Request $request) {
        //Note: password field is handled separately
        $validFields = array('name', 'email', 'dob');

        $payload = $this->getPayload($request);

        $errors = array();
        if($member = $this->memberService->createMember(
            $payload['name'], $payload['email'],
            $payload['password'], new \DateTime($payload['dob']), $errors) === false) {
                $error = array(
                    'human' => 'Unable to validate Member',
                    'code' => 'MemberController.indexPostAction.1',
                    'debug' => $errors,
                );
            return $this->failure($request, 400, $error);
        }

        return $this->success($request, array('member'=>$member->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id, Request $request) {
        if($this->user === null || $this->user->role != Member::SUPER_ADMIN_ROLE) {
            $errors = array(
                'human' => 'You must be a super_user to do this',
                'code' => 'MemberController.indexDeleteAction.1',
            );
            return $this->failure($request, 401, $errors);
        }

        $member = Member::getByPk($id);
        $member->delete();

        return $this->success($request);
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function indexPutAction($id, Request $request) {
        if($this->user === null || $this->user->id != $id) {
            $errors = array(
                'human' => 'This action can only be performed by the owner of the object',
                'code' => 'MemberController.indexPutAction.1',
            );
            return $this->failure($request, 401, $errors);
        }

        $payload = $this->getPayload();

        if(isset($payload['name'])) {
            $this->user->name = $payload['name'];
        }
        if(isset($payload['email'])) {
            $this->user->email = $payload['email'];
        }
        if(isset($payload['dob'])) {
            $this->user->dob = new \DateTime($payload['dob']);
        }
        $this->user->save();

        return $this->success($request, $this->user->getPrivateArray());
    }
}
