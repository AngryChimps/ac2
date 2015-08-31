<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\SampleBundle\services\PublishService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;

class PublishController extends AbstractController
{
    /** @var PublishService */
    protected $publishService;

    public function __construct(RequestStack $requestStack, ResponseService $responseService,
                                PublishService $publishService) {
        parent::__construct($requestStack, $responseService);

        $this->publishService = $publishService;
    }

    public function indexGetAction($region, $companyShortName) {
        return $this->responseService->success($this->publishService->publish($region, $companyShortName));
    }
}
