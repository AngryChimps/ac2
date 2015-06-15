<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\MemberMediaService;
use AngryChimps\ApiBundle\Services\MemberService;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;

class MemberMediaController extends AbstractController
{
    /** @var MemberMediaService */
    protected $memberMediaService;

    /** @var MemberService */
    protected $memberService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, MemberService $memberService,
                                MemberMediaService $memberMediaService) {
        parent::__construct($requestStack, $sessionService, $responseService);
        $this->memberMediaService = $memberMediaService;
        $this->memberService = $memberService;
    }

    public function indexPostAction($memberId) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photo */
        $media = $this->getRequest()->files->get('media');

        if($media === null) {
            $error = array('code' => 'Api.MemberMediaController.indexPostAction.1',
                'human' => 'No media was attached');
            return $this->responseService->failure(400, $error);
        }

        $member = $this->memberService->getMember($memberId);

        if($member === null) {
            $error = array('code' => 'Api.MemberMediaController.indexPostAction.2',
                'human' => 'Unable to locate requested member');
            return $this->responseService->failure(400, $error);
        }

        try {
            $filename = $this->memberMediaService->postMedia($media, $member);
        }
        catch(\Exception $ex) {
            $error = array('code' => 'Api.MemberMediaController.indexPostAction.3',
                'human' => 'Unknown error occurred processing the image');
            return $this->responseService->failure(400, $error, $ex);
        }

        return $this->responseService->success(array('payload' => array('filename' => $filename)));
    }

}
