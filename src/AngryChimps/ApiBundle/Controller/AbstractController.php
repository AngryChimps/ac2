<?php


namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Member;
use NormTests\mysql\Person;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends FOSRestController {
    /** @var  \Norm\riak\Member */
    protected $user;

    protected $content;
    protected $payload;

    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        parent::__construct();

        //Decode the data
        $this->content = json_decode($request->getContent(), true);
        $this->payload = $this->content['payload'];

        //If there is a user authenticated, load it
        if(!empty($request->query->get('auth_token'))) {
            /** @var \AngryChimps\ApiBundle\Services\AuthService $auth */
            $auth = $this->get('angry_chimps_api.auth');
            $this->user = $auth->getUserByAuthToken($request->query->get('auth_token'));
        }

        //Make sure the user is who they say they are
        if($this->user->id !== $request->query->get('user_id')) {
            $this->createAccessDeniedException('Access Denied; code AbstractController.__construct.1');
        }
    }

    private function getView(Request $request, $data, $statusCode,
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
            'data' => $data,
            'errors' => $errors,
            'exception' => $exArr,
            'request' => array(
                            'uri' => $request->getUri(),
                            'method' => $request->getMethod(),
                            'payload' => $this->getContent($request),
            ),
       );
        
        $view = parent::view($return, $statusCode);
        return $view->setFormat('json');
    }

    /**
     * @param Request $request
     * @param Array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function success(Request $request, array $data = array()) {
        $view = $this->getView($request, $data, 200, array(), null);
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param $code
     * @param array $errors
     * @param \Exception $ex
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function failure(Request $request, $code, array $errors, \Exception $ex = null) {
        $view = $this->getView($request, array(), $code, $errors, $ex);
        return $this->handleView($view);
    }

    /**, a
     * @return \Norm\riak\base\Member|\Norm\riak\Member|null
     */
    public function getUser() {
        return $this->user;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function isAuthorizedSelf($user_ids) {
        if(!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }

        if($this->user === null) {
            return false;
        }
        elseif($this->user->role === Member::SUPER_ADMIN_ROLE) {
            return true;
        }
        elseif(!in_array($this->user->id, $user_ids)) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isAuthorizedAdmin() {
        if($this->user === null) {
            return false;
        }
        elseif($this->user->role === Member::SUPER_ADMIN_ROLE) {
            return true;
        }
        else {
            return false;
        }
    }
} 