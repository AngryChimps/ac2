<?php

namespace AngryChimps\MediaBundle\Controller;

use AngryChimps\MediaBundle\Services\MediaService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /** @var MediaService */
    protected $mediaService;

    /** @var \Symfony\Component\HttpFoundation\Request  */
    protected $request;

    public function __construct(RequestStack $requestStack, MediaService $mediaService) {
        $this->mediaService = $mediaService;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function indexAction($photoUrl)
    {
        list($filesystemName, $filename) = explode('/', $photoUrl);

        $responseString = $this->mediaService->retrieveSized($filesystemName, $filename,
            $this->request->query->get('width'), $this->request->query->get('height'));

        // Generate response
        $response = new Response();

        // Set headers
//        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($filename));
//        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
//        $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
//        $response->sendHeaders();

        $response->setContent($responseString);

        return $response;
    }
}
