<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CompanyMediaService;
use AngryChimps\ApiBundle\Services\CompanyService;
use AngryChimps\ApiBundle\Services\ProviderAdService;
use AngryChimps\MediaBundle\Services\MediaService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class CompanyMediaController extends AbstractController
{
    /** @var CompanyMediaService */
    protected $companyMediaService;

    /** @var CompanyService */
    protected $companyService;

    /** @var ProviderAdService */
    protected $providerAdService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CompanyService $companyService,
                                CompanyMediaService $companyMediaService, ProviderAdService $providerAdService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->companyMediaService = $companyMediaService;
        $this->companyService = $companyService;
        $this->providerAdService = $providerAdService;
    }

    public function indexPostAction($companyId, $providerAdId) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $media = $this->getRequest()->files->get('media');

        if($media === null) {
            $error = array('code' => 'Api.CompanyMediaController.indexPostAction.1',
                'human' => 'No photo was attached');
            return $this->responseService->failure(400, $error);
        }

        $company = $this->companyService->getByPk($companyId);

        if($company === null) {
            $error = array('code' => 'Api.CompanyMediaController.indexPostAction.2',
                'human' => 'Unable to locate requested company');
            return $this->responseService->failure(400, $error);
        }

        if($providerAdId !== false) {
            $providerAd = $this->providerAdService->getProviderAd($providerAdId);

            if($providerAd === null) {
                $error = array('code' => 'Api.CompanyMediaController.indexPostAction.3',
                    'human' => 'Unable to locate requested provider ad');
                return $this->responseService->failure(400, $error);
            }
        }
        else {
            $providerAd = null;
        }

        try {
            $filename = $this->companyMediaService->postMedia($company, $providerAd, $media);
        }
        catch(\Exception $ex) {
            $error = array('code' => 'Api.CompanyMediaController.indexPostAction.4',
                'human' => 'Unknown error occurred processing the image');
            return $this->responseService->failure(400, $error);
        }

        return $this->responseService->success(array('payload' => array('filename' => 'ci/' . $filename)));
    }

}
