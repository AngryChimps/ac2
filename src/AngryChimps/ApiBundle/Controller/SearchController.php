<?php

namespace AngryChimps\ApiBundle\Controller;

use AC\NormBundle\Services\LogTestService;
use AngryChimps\ApiBundle\Services\SearchService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class SearchController extends AbstractController
{
    /** @var  \AngryChimps\ApiBundle\Services\SearchService */
    protected $searchService;

    protected $logTester;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, SearchService $searchService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->searchService = $searchService;
    }

    public function indexPostAction() {
        $result = $this->searchService->getSampleProviderAdListing();
        return $this->responseService->success($result);
    }

}
