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
                'code' => 'MemberController.indexGetAction.1'
            );
            return $this->failure(404, $errors);
        }

        $user = $this->getUser();
        if($user !== null && $member->id === $user->id) {
            $memberInfo = $member->getPrivateArray();
        }
        else {
            $memberInfo = $member->getPublicArray();
        }
        $data = array('member' => $memberInfo);

        return $this->success($data);
    }

    /**
     * @Route("/")
     * @Method({"POST"})
     */
    public function indexPostAction() {
        //Note: password field is handled separately
        $validFields = array('name', 'email', 'dob');

        $payload = $this->getPayload();

        $errors = array();
        if($member = $this->getMemberService()->createMember(
            $payload['name'], $payload['email'],
            $payload['password'], new \DateTime($payload['dob']), $errors) === false) {
                $error = array(
                    'human' => 'Unable to validate Member',
                    'code' => 'MemberController.indexPostAction.1',
                    'debug' => $errors,
                );
            return $this->failure(400, $error);
        }

        return $this->success(array('member'=>$member->getPrivateArray()));
    }

    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function indexDeleteAction($id) {
        if($this->isAuthorizedSelf($id)) {
            $errors = array(
                'human' => 'You must be a super_user to do this',
                'code' => 'MemberController.indexDeleteAction.1',
            );
            return $this->failure(401, $errors);
        }

        $member = Member::getByPk($id);

        if($member === null) {
            $errors = array(
                'human' => 'Unable to find the requested member',
                'code' => 'MemberController.indexDeleteAction.2'
            );
            return $this->failure(404, $errors);
        }

        $member->status = Member::DELETED_STATUS;
        $member->save();

        return $this->success();
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
                'code' => 'MemberController.indexPutAction.1',
            );
            return $this->failure(401, $errors);
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

        return $this->success($user->getPrivateArray());
    }
}
