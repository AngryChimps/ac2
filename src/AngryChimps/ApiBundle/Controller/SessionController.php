<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\DeviceService;
use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\ApiBundle\Services\SessionService;
use FOS\RestBundle\Controller\FOSRestController;
use Norm\Device;
use Norm\Session;
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

    public function indexGetAction($id) {
        /** @var Session $session */
        $session = $this->sessionService->get('session', $id);

        return $this->responseService->success(array(
            'session_id' => $session->getId(),
            'user_id' => $session->getUserId(),
            'device_id' => $session->getDeviceId(),
            'browser_hash' => $session->getBrowserHash(),
        ));
    }

    public function indexPostAction() {
        $payload = $this->getPayload();
        $session = $this->sessionService->post('session', $payload);

        return $this->responseService->success(['session' => ['id' => $session->getId()]]);
    }
}
