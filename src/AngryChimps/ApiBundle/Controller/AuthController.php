<?php

namespace AngryChimps\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use \Norm\riak\Member;

/**
 * Class AuthController
 *
 * @Route("/auth")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/login")
     * @Method({"POST"})
     */
    public function loginAction(Request $request)
    {
        $input = $this->getContent($request);
        $userProfile = array();

        /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
        $auth = $this->get('angry_chimps_api.auth');

        //FB Login
        if(isset($input['fb_id'])) {
            try {
                $userProfile = $auth->fbAuth($input['fb_id'], $input['access_token']);
            }
            catch(\FacebookApiException $fbex) {
                $error = array('code' => 'AuthController.loginAction.1',
                               'human' => 'Unable to authenticate token to Facebook');
                return $this->failure($request, 400, $error, $fbex);
            }
            catch(\Exception $ex) {
                $error = array('code' => 'AuthController.loginAction.2',
                    'human' => 'Unable to authenticate for unknown reasons');
                return $this->failure($request, 400, $error, $ex);
            }

            $user = Member::getByEmail($userProfile['email']);

            if($user === null) {
                $user = $auth->registerFbUser($userProfile);
                $is_new = true;
            }
            else {
                $is_new = false;
            }

            $data = array('user' => $user->getPrivateArray(),
                          'is_new' => $is_new,
                          'php_session_id' => session_id(),
                          'auth_token' => $auth->generateAuthToken(),
            );
            return $this->success($request, $data);
        }
        elseif(isset($input['email'])) {
            if($user = $auth->loginFormUser($input['email'], $input['password'])) {
                $data = array('user' => $user->getPrivateArray(),
                    'is_new' => false,
                    'php_session_id' => session_id(),
                    'auth_token' => $auth->generateAuthToken(),
                );
                return $this->success($request, $data);

            }
            else {
                $error = array('code' => 'AuthController.loginAction.4',
                    'human' => 'Either the email was not found or the password did not match');
                return $this->failure($request, 400, $error);
            }
        }


        //Invalid request (doesn't specify email or fb_id)
        $error = array('code' => 'AuthController.loginAction.3',
                        'human' => 'Invalid request.  You must specify either email or fb_id');
        return $this->failure($request, 400, $error);
    }

    /**
     * @Route("/logout")
     * @Method({"GET"})
     */
    public function logoutAction(Request $request)
    {
        session_regenerate_id();

        return $this->success($request);
    }

    /**
     * @Route("/changePassword")
     * @Method({"POST"})
     */
    public function changePasswordAction(Request $request)
    {
        /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
        $auth = $this->get('angry_chimps_api.auth');
        $old = $this->payload['old_password'];
        $new = $this->payload['new_password'];

        //Check old password make sure it is correct
        if(!$auth->isPasswordCorrect($this->user, $old)) {
            $error = array('code' => 'AuthController.loginAction.3',
                'human' => 'Invalid request.  You must specify either email or fb_id');
            return $this->failure($request, 400, $error);
        }

        $this->user->password = $auth->hashPassword($new);

        return $this->success($request);
    }

    /**
     * @Route("/forgotPassword")
     * @Method({"POST"})
     */
    public function forgotPasswordAction(Request $request)
    {
        /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
        $auth = $this->get('angry_chimps_api.auth');
        $email = $this->payload['email'];

        $auth->forgotPassword($email);

        return $this->success($request);
    }

    /**
     * @Route("/forgotPasswordReset")
     * @Method({"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordReset(Request $request) {
        /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
        $auth = $this->get('angry_chimps_api.auth');
        $email = $this->payload['email'];
        $password = $this->payload['password'];

        if(strlen($password) < $auth::MINIMUM_PASSWORD_LENGTH) {
            $errors = array(
                'human' => 'Password must be at least ' . $auth::MINIMUM_PASSWORD_LENGTH
                            . ' characters long',
                'code' => 'AuthController.forgotPasswordReset.1'
            );
            return $this->failure($request, 400, $errors);
        }

        $auth->resetPassword($email, $password);

        return $this->success($request);
    }
}
