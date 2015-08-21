<?php

namespace AngryChimps\SampleBundle\Controller;

use AngryChimps\ApiBundle\Services\ResponseService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    /** @var  RequestStack */
    protected $request;

    /** @var  ResponseService */
    protected $responseService;

    private $payload;


    public function __construct(RequestStack $requestStack, ResponseService $responseService) {
        $this->request = $requestStack->getCurrentRequest();
        $this->responseService = $responseService;
    }

    public function getPayload() {
        if($this->payload === null) {
            $this->content = json_decode($this->request->getContent(), true);
            $this->payload = $this->content['payload'];
        }
        return $this->payload;
    }
}
