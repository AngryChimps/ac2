<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\SampleBundle\services\CompanyService;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;

class CompanyController extends AbstractController
{
    /** @var CompanyService */
    protected $companyService;

    public function __construct(RequestStack $requestStack, ResponseService $responseService,
                                CompanyService $companyService) {
        parent::__construct($requestStack, $responseService);

        $this->companyService = $companyService;
    }

    public function indexGetAllAction($region) {
        $shortNames = $this->companyService->getAllShortNames($region);
        return $this->responseService->success(['company_short_names'=> $shortNames]);
    }

    public function indexPostAction($region, $companyShortName) {
        $this->companyService->createFolders($region, $companyShortName);

        $payload = $this->getPayload();
        $this->companyService->post($region, $companyShortName, $payload);

        return $this->responseService->success();
    }

    public function indexGetAction($region, $companyShortName) {
        $data = $this->companyService->get($region, $companyShortName);

        return $this->responseService->success(['company' => $data]);
    }

    public function indexDeleteAction($region, $companyShortName) {
        $this->companyService->delete($region, $companyShortName);

        return $this->responseService->success();
    }

    public function indexPutAction($region, $companyShortName) {
        $payload = $this->getPayload();
        $this->companyService->put($region, $companyShortName, $payload);

        return $this->responseService->success();
    }
}
