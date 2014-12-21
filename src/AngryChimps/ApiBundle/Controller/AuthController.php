<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\MemberService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use \Norm\riak\Member;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use AngryChimps\ApiBundle\Services\AuthService;

/**
 * Class AuthController
 *
 * @Route("/auth")
 */
class AuthController extends AbstractController
{
    protected $authService;
    protected $debugStatus;

    /** @var MemberService  */
    protected $memberService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, AuthService $authService,
                                $debugStatus, MemberService $memberService)
    {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->authService = $authService;
        $this->debugStatus = $debugStatus;
        $this->memberService = $memberService;
    }

    public function registerAction() {
        $payload = $this->getPayload();

        $member = $this->memberService->getMemberByEmailEnabled($payload['email']);
        if($member !== null) {
            $error = array(
                'human' => 'Active member found with that email',
                'code' => 'Api.MemberController.indexPostAction.1',
            );
            return $this->responseService->failure(400, $error);
        }

        $errors = array();
        $member = $this->authService->register(
            $payload['name'], $payload['email'],
            $payload['password'], new \DateTime($payload['dob']), $errors);
        if($member === false) {
            $error = array(
                'human' => 'Unable to validate Member',
                'code' => 'Api.MemberController.indexPostAction.2',
                'debug' => (string) $errors,
            );
            return $this->responseService->failure(400, $error);
        }

        return $this->responseService->success(array('member'=>array('id'=>$member->id),
            'auth_token' => $this->authService->generateToken()));
    }

    /**
     * @Route("/login")
     * @Method({"POST"})
     */
    public function loginAction()
    {
        $payload = $this->getPayload();
        $auth = $this->authService;

        $user = $auth->loginFormUser($payload['email'], $payload['password']);
        if($user !== false && $user !== null && $user->id !== null) {
            //Set the userId in the session
            $this->getSessionService()->setSessionUser($user);

            $data = array('member' => array('id'=> $user->id,
                                            'name' => $user->name,
                                            'photo' => $user->photo,
                                            'email' => $user->email,
                                            'company_ids' => $user->managedCompanyIds,
                ));
            return $this->responseService->success($data);
        }
        else {
            $error = array('code' => 'Api.AuthController.loginAction.1',
                'human' => 'Either the email was not found or the password did not match');

            if($this->debugStatus) {
                if($user === null) {
                    $error['debug'] = 'User was not found';
                }
                else {
                    $error['debug'] = 'Password did not match';
                }
            }

            return $this->responseService->failure(400, $error);
        }
    }

//    public function fbLoginRegisterAction() {
//        $payload = $this->getPayload();
//        $auth = $this->authService;
//
//        try {
//            $userProfile = $this->authService->fbAuth($payload['fb_id'], $payload['fb_access_token']);
//        }
//        catch(\FacebookApiException $fbex) {
//            $error = array('code' => 'Api.AuthController.fbLoginRegisterAction.1',
//                'human' => 'Unable to authenticate token to Facebook');
//            return $this->responseService->failure(401, $error, $fbex);
//        }
//        catch(\Exception $ex) {
//            $error = array('code' => 'Api.AuthController.fbLoginRegisterAction.2',
//                'human' => 'Unable to authenticate for unknown reasons');
//            return $this->responseService->failure(401, $error, $ex);
//        }
//
//        $user = Member::getByEmail($userProfile['email']);
//
//        if($user === null) {
//            $user = $auth->registerFbUser($userProfile);
//            $is_new = true;
//        }
//        else {
//            $is_new = false;
//        }
//
//        $data = array('user' => $user->getPrivateArray(),
//            'is_new' => $is_new,
//            'auth_token' => $auth->generateToken(),
//        );
//        return $this->responseService->success($data);
//    }

    /**
     * @Route("/logout")
     * @Method({"GET"})
     */
    public function logoutAction()
    {
        $this->getSessionService()->logoutUser();

        return $this->responseService->success();
    }

    /**
     * @Route("/changePassword")
     * @Method({"POST"})
     */
    public function changePasswordAction()
    {
        $auth = $this->authService;
        $payload = $this->getPayload();
        $old = $payload['old_password'];
        $new = $payload['new_password'];
        $user = $this->getAuthenticatedUser();

        //Check old password make sure it is correct
        if(!$auth->isPasswordCorrect($user, $old)) {
            $error = array('code' => 'Api.AuthController.changePasswordAction.1',
                'human' => 'Invalid request.  You must specify either email or fb_id');
            return $this->responseService->failure(400, $error);
        }

        $user->password = $auth->hashPassword($new);

        return $this->responseService->success();
    }

    /**
     * @Route("/forgotPassword")
     * @Method({"POST"})
     */
    public function forgotPasswordAction()
    {
        $auth = $this->authService;
        $payload = $this->getPayload();
        $email = $payload['email'];

        $auth->forgotPassword($email);

        return $this->responseService->success();
    }

    /**
     * @Route("/forgotPasswordReset")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordReset() {
        $auth = $this->authService;
        $payload = $this->getPayload();
        $email = $payload['email'];
        $password = $payload['password'];

        if(strlen($password) < $auth::MINIMUM_PASSWORD_LENGTH) {
            $errors = array(
                'human' => 'Password must be at least ' . $auth::MINIMUM_PASSWORD_LENGTH
                            . ' characters long',
                'code' => 'Api.AuthController.forgotPasswordReset.1'
            );
            return $this->responseService->failure(400, $errors);
        }

        $auth->resetPassword($email, $password);

        return $this->responseService->success();
    }
}
