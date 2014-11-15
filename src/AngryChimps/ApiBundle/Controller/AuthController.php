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
                $view = $this->getView($request, array(), 400, $error, $fbex)
                    ->setFormat('json');

                return $this->handleView($view);
            }
            catch(\Exception $ex) {
                $error = array('code' => 'AuthController.loginAction.2',
                    'human' => 'Unable to authenticate for unknown reasons');
                $view = $this->getView($request, array(), 400, $error, $ex)
                    ->setFormat('json');

                return $this->handleView($view);
            }

            $user = Member::getByEmail($userProfile['email']);

            if($user === null) {
                $user = $auth->registerFbUser($userProfile);
                $is_new = true;
            }
            else {
                $is_new = false;
            }

            $data = array('user' => $user->getPrivateArray(), 'is_new' => $is_new);
            $view = $this->getView($request, $data, 200)->setFormat('json');

            return $this->handleView($view);

        }


        //Invalid request (doesn't specify email or fb_id)
        $error = array('code' => 'AuthController.loginAction.3',
                        'human' => 'Invalid request.  You must specify either email or fb_id');
        $view = $this->getView($request, array(), 400, $error);

        return $this->handleView($view);
    }

    /**
     * @Route("/logout")
     * @Template()
     */
    public function logoutAction()
    {
        return array(
                // ...
            );    }

    /**
     * @Route("/changePassword")
     * @Template()
     */
    public function changePasswordAction()
    {
        return array(
                // ...
            );    }

    /**
     * @Route("/forgotPassword")
     * @Template()
     */
    public function forgotPasswordAction()
    {
        return array(
                // ...
            );    }

}
