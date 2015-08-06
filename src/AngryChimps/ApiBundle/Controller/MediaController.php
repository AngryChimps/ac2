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

    public function indexGetAction($entityName, $id, $filename)
    {
        switch($entityName) {
            case 'location':
                $filesystemName = 'location_images_fs';
                break;
            case 'member':
                $filesystemName = 'member_images_fs';
                break;
            default:
                throw new \Exception('Unsupported entity type: ' . $entityName);
        }

        $responseString = $this->mediaService->retrieveSized($filesystemName, $filename,
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

    public function indexPostAction($entityName, $id)
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $media */
//        throw new \Exception($this->getRequest()->files->count());
        $media = $this->getRequest()->files->get('media');

        if($media === null) {
            return $this->responseService->failure(400, ResponseService::MEDIA_NOT_ATTACHED);
        }

        switch($entityName) {
            case 'member':
                /** @var Member $member */
                $member = $this->memberService->get('member', $id);
                if($member === null) {
                    return $this->responseService->failure(400, ResponseService::INVALID_MEMBER_ID);
                }
                $filename = $this->mediaService->persist('member_images_fs', $media);
                $this->memberService->patch($member, ['photo' => $filename]);
                break;

            case 'location':
                /** @var Location $location */
                $location = $this->locationService->get('location', $id);
                if($location === null) {
                    return $this->responseService->failure(400, ResponseService::INVALID_LOCATION_ID);
                }
                $filename = $this->mediaService->persist('location_images_fs', $media);
                $photos = $location->getPhotos();
                $photos[] = $filename;
                $this->locationService->patch($location, ['photos' => $photos]);
                break;

            default:
                return $this->responseService->failure(400, ResponseService::UNKNOWN_ENTITY_TYPE);
        }

        return $this->responseService->success(array('payload' => array('media' => ['id' => $filename])));
    }

    public function indexPatchAction($id)
    {
        return $this->getPatchResponse('company', $id);
    }

    public function indexDeleteAction($id)
    {
        return $this->getDeleteResponse('company', $id);
    }
}
