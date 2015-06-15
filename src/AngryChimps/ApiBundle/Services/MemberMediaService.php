<?php


namespace AngryChimps\ApiBundle\services;


use AngryChimps\MediaBundle\Services\MediaService;
use AngryChimps\NormBundle\services\NormService;
use Norm\Member;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MemberMediaService {

    /** @var MediaService */
    protected $mediaService;

    /** @var NormService */
    protected $norm;

    public function __construct(MediaService $mediaService, NormService $norm) {
        $this->mediaService = $mediaService;
        $this->norm = $norm;
    }
    public function postMedia(UploadedFile $file, Member $member) {
        $filename = 'mi/' . $this->mediaService->persist('member_images_fs', $file);
        $member->photo = $filename;
        $this->norm->update($member);

        return $filename;
    }
}