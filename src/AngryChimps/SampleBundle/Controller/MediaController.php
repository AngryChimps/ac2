<?php

namespace AngryChimps\SampleBundle\Controller;


use AngryChimps\ApiBundle\Services\ResponseService;
use AngryChimps\SampleBundle\services\MediaService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{
    /** @var MediaService */
    protected $mediaService;

    public function __construct(RequestStack $requestStack, ResponseService $responseService,
                                MediaService $mediaService) {
        parent::__construct($requestStack, $responseService);

        $this->mediaService = $mediaService;
    }

    public function indexGetAction($region, $companyShortName, $name) {
        $responseString = $this->mediaService->get($region, $companyShortName, $name);

        // Generate response
        $response = new Response();

        // Set headers
        $response->headers->set('Content-type', 'image/jpeg');

        $response->setContent($responseString);

        return $response;
    }

    public function indexPostAction($region, $companyShortName) {
        $media = $this->request->files->get('media');

        if($media === null) {
            return $this->responseService->failure(400, ResponseService::MEDIA_NOT_ATTACHED);
        }

        $payload = $this->getPayload();

        $this->mediaService->post($region, $companyShortName, $payload['name'], $media, $payload['top_right'],
            $payload['top_left'], $payload['bottom_right'], $payload['bottom_left']);

        return $this->responseService->success();
    }

    public function indexDeleteAction($region, $companyShortName, $name) {
        $this->mediaService->delete($region, $companyShortName, $name);

        return $this->responseService->success();
    }
}
