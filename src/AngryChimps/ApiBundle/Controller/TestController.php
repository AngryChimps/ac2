<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpKernel\Kernel;

class TestController extends AbstractController
{
    protected $debug;

    /** @var Kernel */
    protected $kernel;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface  */
    protected $container;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, $debug, Kernel $kernel) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->debug = $debug;
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
    }

    public function envAction() {
        $data = [
            'debug' => $this->debug,
            'env' => $this->kernel->getEnvironment(),
            'web_profiler.debug_toolbar.mode' => $this->container->getParameter('web_profiler.debug_toolbar.mode'),
        ];
        return $this->responseService->success($data);
    }
}
