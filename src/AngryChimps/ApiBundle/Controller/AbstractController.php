<?php


namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Member;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends FOSRestController {
    /** @var  \Norm\riak\Member */
    private $user;

    private $content;
    private $payload;
    private $request;

    /**
     * @return Request
     */
    public function getRequest() {
        if($this->request === null) {
            $this->request = $this->container->get('request_stack')->getCurrentRequest();
        }
        return $this->request;
    }
    protected function getPayload() {
        if($this->payload === null) {
            $this->content = json_decode($this->getRequest()->getContent(), true);
            $this->payload = $this->content['payload'];
        }
        return $this->payload;
    }

    private function getView($data, $statusCode,
                            array $errors = array(),
                            \Exception $ex = null)
    {
        if($ex === null) {
            $exArr = array();
        }
        else {
            $exArr = array('type' => get_class($ex),
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'stack' => $ex->getTraceAsString(),
            );
        }

        $return = array(
            'payload' => $data,
            'php_session_id' => session_id(),
            'errors' => $errors,
            'exception' => $exArr,
            'request' => array(
                            'uri' => $this->getRequest()->getUri(),
                            'method' => $this->getRequest()->getMethod(),
                            'payload' => $this->getPayload(),
            )
       );
        
        $view = parent::view($return, $statusCode);
        return $view->setFormat('json');
    }

    /**
     * @param Array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function success(array $data = array()) {
        $view = $this->getView($data, 200, array(), null);
        return $this->handleView($view);
    }

    /**
     * @param $code
     * @param array $errors
     * @param \Exception $ex
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function failure($code, array $errors, \Exception $ex = null) {
        $view = $this->getView(array(), $code, $errors, $ex);
        return $this->handleView($view);
    }

    /**
     * @return \Norm\riak\base\Member|\Norm\riak\Member|null
     */
    public function getUser() {
        $request = $this->getRequest();
        //If there is a user authenticated, load it
        $authToken = $request->headers->get($this->container->getParameter('auth_header_name'));
        if(!empty($authToken)) {
            /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
            $auth = $this->get('angry_chimps_api.auth');
            $this->user = $auth->getUserByAuthToken($authToken);

            //If there's an auth token there should be a user
            if($this->user === null) {
                $this->createAccessDeniedException('Access Denied; code AbstractController.getUser.1');
            }

            //Make sure the user is who they say they are
            if($this->user->id != $request->query->get('user_id')) {
                $this->createAccessDeniedException('Access Denied; code AbstractController.getUser.2');
            }
        }

        return $this->user;
    }

    public function isAuthorizedSelf($user_ids) {
        if(!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }

        $user = $this->getUser($this->getRequest());
        if($user === null) {
            return false;
        }
        elseif($user->role === Member::SUPER_ADMIN_ROLE) {
            return true;
        }
        elseif(!in_array($user->id, $user_ids)) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isAuthorizedAdmin() {
        $user = $this->getUser($this->getRequest());
        if($user === null) {
            return false;
        }
        elseif($user->role === Member::SUPER_ADMIN_ROLE) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return \AngryChimps\ApiBundle\Services\AuthService
     */
    public function getAuthService() {
        return $this->container->get('angry_chimps_api.auth');
    }
    /**
     * @return \AngryChimps\ApiBundle\Services\CategoriesService
     */
    public function getCategoriesService() {
        return $this->container->get('angry_chimps_api.categories');
    }
    /**
     * @return \AngryChimps\ApiBundle\Services\CompanyService
     */
    public function getCompanyService() {
        return $this->container->get('angry_chimps_api.company');
    }
    /**
     * @return \AngryChimps\ApiBundle\Services\LocationService
     */
    public function getLocationService() {
        return $this->container->get('angry_chimps_api.location');
    }
    /**
     * @return \AngryChimps\ApiBundle\Services\MemberService
     */
    public function getMemberService() {
        return $this->container->get('angry_chimps_api.member');
    }
    /**
     * @return \AngryChimps\ApiBundle\Services\ServiceService
     */
    public function getServiceService() {
        return $this->container->get('angry_chimps_api.service');
    }
}