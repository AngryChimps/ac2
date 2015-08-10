<?php

namespace AngryChimps\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;

class AnimalController extends AbstractController
{
    public function indexGetAction() {
        $json = '
[
    {"id": 1000, "name": "Dogs"},
    {"id": 2000, "name": "Cats"},
    {"id": 3000, "name": "Birds"},
    {"id": 4000, "name": "Horses"},
    {"id": 5000, "name": "Reptiles"},
    {"id": 6000, "name": "Small Animals"},
    {"id": 7000, "name": "Fish"},
    {"id": 8000, "name": "Amphibians"},
    {"id": 9000, "name": "Livestock"}
]
     ';

        return $this->responseService->success(['animals' => json_decode($json, true)]);
    }
}
