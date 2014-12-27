<?php


namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\SessionService;
use Norm\riak\Member;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;

class AbstractController {
    /** @var  \Norm\riak\Member */
    private $user;

    /** @var  RequestStack */
    private $request;

    /**
     * @return \AngryChimps\ApiBundle\Services\SessionService
     */
    private $sessionService;


    private $payload;
    private $content;

    /** @var  ResponseService */
    protected $responseService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->sessionService = $sessionService;
        $this->responseService = $responseService;

        //Check session information
        if(static::class !== 'AngryChimps\ApiBundle\Controller\SessionController'
            || $this->getRequest()->getMethod() !== 'GET') {
            $this->sessionService->checkToken();
        }

        //Get the authenticated user if there is one
        if($this->request->query->get('userId')) {
            $this->user = $this->sessionService->getSessionUser();
        }
    }

    protected function getPayload()
    {
        if($this->payload === null) {
            $this->content = json_decode($this->request->getContent(), true);
            $this->payload = $this->content['payload'];
        }
        return $this->payload;
    }

    /**
     * @return Request
     */
    protected function getRequest() {
        return $this->request;
    }

    /**
     * @return \Norm\riak\Member|null
     */
    public function getAuthenticatedUser() {
        return $this->user;
    }

    /**
     * @return SessionService
     */
    public function getSessionService() {
        return $this->sessionService;
    }

    public function isAuthorizedSelf($user_ids) {
        if(!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }

        $user = $this->getAuthenticatedUser();
        if($user === null || !in_array($user->id, $user_ids)) {
            return false;
        }

        return true;
    }

    public function isAuthorizedAdmin() {
        $user = $this->getAuthenticatedUser();
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
}