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
    public function loginAction()
    {
        $payload = $this->getPayload();
        $auth = $this->getAuthService();

        //FB Login
        if(isset($payload['fb_id'])) {
            try {
                $userProfile = $auth->fbAuth($payload['fb_id'], $payload['fb_access_token']);
            }
            catch(\FacebookApiException $fbex) {
                $error = array('code' => 'Api.AuthController.loginAction.1',
                               'human' => 'Unable to authenticate token to Facebook');
                return $this->failure(401, $error, $fbex);
            }
            catch(\Exception $ex) {
                $error = array('code' => 'Api.AuthController.loginAction.2',
                    'human' => 'Unable to authenticate for unknown reasons');
                return $this->failure(401, $error, $ex);
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
                          'auth_token' => $auth->generateToken(),
            );
            return $this->success($data);
        }
        elseif(isset($payload['email'])) {
            $user = $auth->loginFormUser($payload['email'], $payload['password']);
            if($user !== false && $user !== null ) {
                $data = array('member' => $user->getPrivateArray(),
                              'is_new' => false,
                              'auth_token' => $auth->generateToken(),
                );
                return $this->success($data);

            }
            else {
                $error = array('code' => 'Api.AuthController.loginAction.3',
                    'human' => 'Either the email was not found or the password did not match');
                return $this->failure(400, $error);
            }
        }


        //Invalid request (doesn't specify email or fb_id)
        $error = array('code' => 'Api.AuthController.loginAction.4',
                       'human' => 'Invalid request.  You must specify either email or fb_id');
        return $this->failure(400, $error);
    }

    /**
     * @Route("/logout")
     * @Method({"GET"})
     */
    public function logoutAction()
    {
        session_regenerate_id();

        return $this->success();
    }

    /**
     * @Route("/changePassword")
     * @Method({"POST"})
     */
    public function changePasswordAction()
    {
        $auth = $this->getAuthService();
        $payload = $this->getPayload();
        $old = $payload['old_password'];
        $new = $payload['new_password'];
        $user = $this->getUser();

        //Check old password make sure it is correct
        if(!$auth->isPasswordCorrect($user, $old)) {
            $error = array('code' => 'Api.AuthController.changePasswordAction.1',
                'human' => 'Invalid request.  You must specify either email or fb_id');
            return $this->failure(400, $error);
        }

        $user->password = $auth->hashPassword($new);

        return $this->success();
    }

    /**
     * @Route("/forgotPassword")
     * @Method({"POST"})
     */
    public function forgotPasswordAction()
    {
        $auth = $this->getAuthService();
        $payload = $this->getPayload();
        $email = $payload['email'];

        $auth->forgotPassword($email);

        return $this->success();
    }

    /**
     * @Route("/forgotPasswordReset")
     * @Method({"POST"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forgotPasswordReset() {
        $auth = $this->getAuthService();
        $payload = $this->getPayload();
        $email = $payload['email'];
        $password = $payload['password'];

        if(strlen($password) < $auth::MINIMUM_PASSWORD_LENGTH) {
            $errors = array(
                'human' => 'Password must be at least ' . $auth::MINIMUM_PASSWORD_LENGTH
                            . ' characters long',
                'code' => 'Api.AuthController.forgotPasswordReset.1'
            );
            return $this->failure(400, $errors);
        }

        $auth->resetPassword($email, $password);

        return $this->success();
    }

    /**
     * @Route("/getNewSession")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getNewSession() {
        return $this->success(array('session_id' => $this->getAuthService()->getNewSessionToken()));
    }
}
