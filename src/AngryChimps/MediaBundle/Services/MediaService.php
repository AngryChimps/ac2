<?php


namespace AngryChimps\MediaBundle\Services;

use AngryChimps\ApiBundle\Services\AbstractRestService;
use Aws\S3\S3Client;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use Norm\Member;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaService {
    protected $environment;

    protected $filesystemMap;
    protected $filesystemInfo;

    public function __construct($environment, FilesystemMap $filesystemMap, array $filesystemInfo) {
        $this->environment = $environment;
        $this->filesystemMap = $filesystemMap;
        $this->filesystemInfo = $filesystemInfo;
    }

    public function isOwner($obj, Member $authenticatedMember)
    {
        return false;
    }

    public function post($media)
    {
        $filesystem = $this->filesystemMap->get('media_fs');
        $content = file_get_contents($media->getRealPath());
//        $extension = $file->getClientOriginalExtension();
        $imagine = new \Imagine\Imagick\Imagine();
        $img = $imagine->load($content);
        $jpg = $img->get('jpg');
        $filename = sha1(microtime(true) . bin2hex(openssl_random_pseudo_bytes(16)));

        $filesystem->write($filename . '.jpg', $jpg);

        return $filename . '.jpg';
    }


    public function retrieve($fsName, $filename) {
        $filesystem = $this->filesystemMap->get($fsName);
        return $filesystem->read($filename);
    }

    public function retrieveSized($fsName, $filename, $newWidth, $newHeight) {
        $contents = $this->retrieve($fsName, $filename);
        $imagine = new Imagine();
        $image = $imagine->load($contents);

        if(!empty($newWidth) && !empty($newHeight)) {
            $image->resize(new Box($newWidth, $newHeight));
        }

        return $image->get('jpg');
    }
}