<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\DeviceService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\SessionService;
use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Device;
use Norm\riak\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SessionController
 *
 * @Route("/session")
 */
class SessionController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\DeviceService */
    protected $deviceService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, DeviceService $deviceService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->deviceService = $deviceService;
    }

    /**
     * @Route("")
     * @Route("/")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexGetAction() {
        $session = $this->getSessionService()->getNewSession();

        return $this->responseService->success(array(
            'session_id' => $session->getId(),
        ));
    }

    public function indexPostAction() {
        $payload = $this->getPayload();
        $session = $this->sessionService->getNewSession();
        $device = $this->deviceService->register($session, $payload['type'], $payload['push_token'], $payload['description']);

        return $this->responseService->success(array(
            'session_id' => $session->getId(),
        ));
    }
}
