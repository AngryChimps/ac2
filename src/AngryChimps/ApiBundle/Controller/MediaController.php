<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\LocationService;
use AngryChimps\ApiBundle\Services\MemberService;
use AngryChimps\MediaBundle\Services\MediaService;
use Norm\Location;
use Norm\Member;
use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{
    /** @var MemberService  */
    protected $memberService;

    /** @var LocationService  */
    protected $locationService;

    /** @var MediaService */
    protected $mediaService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, MemberService $memberService,
                                LocationService $locationService, MediaService $mediaService) {
        parent::__construct($requestStack, $sessionService, $responseService);

        $this->memberService = $memberService;
        $this->locationService = $locationService;
        $this->mediaService = $mediaService;
    }

    public function indexGetAction($filename)
    {
        $responseString = $this->mediaService->retrieveSized('media_fs', $filename,
            $this->request->query->get('width'), $this->request->query->get('height'));

        // Generate response
        $response = new Response();

        // Set headers
//        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'image/jpeg');
//        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
//        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
//        $response->sendHeaders();

        $response->setContent($responseString);

        return $response;

    }

    public function indexPostAction()
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $media */
//        throw new \Exception($this->getRequest()->files->count());
        $media = $this->getRequest()->files->get('media');

        if($media === null) {
            return $this->responseService->failure(400, ResponseService::MEDIA_NOT_ATTACHED);
        }

        $filename = $this->mediaService->post($media);
        return $this->responseService->success(array('payload' => array('media' => ['id' => $filename])));
    }
}
