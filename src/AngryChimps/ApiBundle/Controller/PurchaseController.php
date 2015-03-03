<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\PurchaseService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class PurchaseController extends AbstractController
{
    protected $purchaseService;
    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                   ResponseService $responseService, PurchaseService $purchaseService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->purchaseService = $purchaseService;
    }

    public function indexGetAction($purchaseId) {

    }

    public function indexPostAction() {

    }

    public function indexPutAction($purchaseId) {

    }

    public function indexDeleteAction($purchaseId) {

    }
}
