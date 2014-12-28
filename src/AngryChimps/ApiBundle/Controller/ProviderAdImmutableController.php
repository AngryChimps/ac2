<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\ProviderAdImmutableService;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\View\ViewHandler;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use AngryChimps\ApiBundle\Services\ResponseService;

class ProviderAdImmutableController extends AbstractController
{

    /** @var ProviderAdImmutableService */
    protected $providerAdImmutableService;


    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService,
                                ProviderAdImmutableService $providerAdImmutableService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->providerAdImmutableService = $providerAdImmutableService;
    }
    public function indexGetAction($providerAdImmutableId) {
        $providerAdImmutable = $this->providerAdImmutableService->getProviderAdImmutable($providerAdImmutableId);

        if($providerAdImmutable === null) {
            $error = array('code' => 'Api.ProviderAdImmutableController.indexGetAction.1',
                'human' => 'Unable to find the given immutable provider ad');
            return $this->responseService->failure(404, $error);
        }

        $data = $this->providerAdImmutableService->getData($providerAdImmutable);

        return $this->responseService->success(array('payload' => $data));
    }
}
