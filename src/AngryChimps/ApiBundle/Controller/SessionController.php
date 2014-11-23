<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SessionController
 *
 * @Route("/session")
 */
class SessionController extends AbstractController
{
    /**
     * @Route("")
     * @Route("/")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexGetAction() {
        $token = $this->getSessionService()->getNewSessionToken();

        $session = new Session();
        $session->id= $token;
        $session->browserHash = $this->getSessionService()->getBrowserHash($this->getRequest());
        $session->save();

        return $this->responseService->success(array('session_id' => $token));
    }

}
