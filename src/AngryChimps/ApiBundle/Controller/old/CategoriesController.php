<?php

namespace AngryChimps\ApiBundle\Controller;

use AngryChimps\ApiBundle\Services\CategoriesService;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use AngryChimps\ApiBundle\Services\ResponseService;

class CategoriesController extends AbstractController
{
    protected $categoriesService;
    protected $responseService;

    public function __construct(RequestStack $requestStack, SessionService $sessionService,
                                ResponseService $responseService, CategoriesService $categoriesService) {
        $this->categoriesService = $categoriesService;
        $this->responseService = $responseService;
        parent::__construct($requestStack, $sessionService, $responseService);
    }
    /**
     * @Route("")
     * @Method({"GET"})
     */
    public function indexGetAction() {
        $data = $this->categoriesService->getCategories();
        return $this->responseService->success(array('categories' => $data));
    }
}
