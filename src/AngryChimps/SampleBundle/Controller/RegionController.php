<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;

class RegionController extends AbstractController
{
    public function __construct(RequestStack $requestStack, ResponseService $responseService) {
        parent::__construct($requestStack, $responseService);
    }

    public function indexGetAllAction() {
        return $this->responseService->success(['regions' => [
            'sf'
        ]]);
    }
}
