<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Norm\riak\Member;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpKernel\Kernel;
use AngryChimps\NormBundle\services\NormService;
use AC\NormBundle\Services\CreatorService;

class TestController extends AbstractController
{
    protected $debug;

    /** @var Kernel */
    protected $kernel;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface  */
    protected $container;

    /** @var NormService */
    protected $norm;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, $debug, Kernel $kernel, NormService $norm) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->debug = $debug;
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
        $this->norm = $norm;
    }

    public function runAction($methodName) {
        return $this->{$methodName}();
    }

    public function envAction() {
        $data = [
            'debug' => $this->debug,
            'env' => $this->kernel->getEnvironment(),
            'web_profiler.debug_toolbar.mode' => $this->container->getParameter('web_profiler.debug_toolbar.mode'),
        ];
        return $this->responseService->success($data);
    }

    public function createAction() {
        $member = new Member();
        $member->setFname('Bob');
        $this->riak->create($member);
        return $this->responseService->success(['id' => $member->getId()]);
    }

    public function retrieveAction($id){
        $member = $this->riak->getMember($id);
        return $this->responseService->success(['fname' => $member->getFname()]);
    }

    protected function normGenerate() {
        /** @var CreatorService $cs */
        $cs = $this->kernel->getContainer()->get('ac_norm.creator');
        $cs->createIfNecessary(true);
        return $this->responseService->success();
    }

    protected function riakCreateType() {
        $datastoreName = 'riak_ds';

        /** @var InfoService $infoService */
        $infoService = $this->kernel->getContainer()->get('ac_norm.info');
        $typeName = $infoService->getDatastorePrefix($datastoreName) . 'class_maps';

        system("riak-admin bucket-type create $typeName '{\"props\":{\"datatype\":\"map\"}}'");
        system("riak-admin bucket-type activate $typeName");
    }

}
