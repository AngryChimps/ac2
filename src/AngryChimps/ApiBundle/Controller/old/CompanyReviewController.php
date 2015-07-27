<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyReviewService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class CompanyReviewController extends AbstractController
{
    /** @var CompanyReviewService */
    protected $companyReviewService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CompanyReviewService $companyReviewService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->companyReviewService = $companyReviewService;
    }
    public function indexGetAction($companyId) {
        jj
    }
}
