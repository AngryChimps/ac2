<?php


namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
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
} 