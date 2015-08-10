<?php

namespace AngryChimps\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use AngryChimps\ApiBundle\Services\SessionService;
use Psr\Log\LoggerInterface;
use AngryChimps\ApiBundle\Services\ResponseService;

class ServiceController extends AbstractController
{
    public function indexGetAction() {
        $json = '
[
    {"id": 100, "name": "Acupuncture"},
    {"id": 200, "name": "Anesthesia"},
    {"id": 300, "name": "Bathing"},
    {"id": 400, "name": "Behavioral Medicine"},
    {"id": 500, "name": "Boarding"},
    {"id": 600, "name": "Dental Care"},
    {"id": 700, "name": "Emergencies"},
    {"id": 800, "name": "Endoscopy"},
    {"id": 900, "name": "Euthanasia"},
    {"id": 1000, "name": "Holistic Care"},
    {"id": 1100, "name": "Internal Medicine"},
    {"id": 1200, "name": "Laboratory"},
    {"id": 1300, "name": "Microchipping"},
    {"id": 1400, "name": "Nutrition"},
    {"id": 1500, "name": "Oncology"},
    {"id": 1600, "name": "Pain Management"},
    {"id": 1700, "name": "Parasites"},
    {"id": 1800, "name": "Pharmacy"},
    {"id": 1900, "name": "Radiology"},
    {"id": 2000, "name": "Senior Care"},
    {"id": 2100, "name": "Spay/Neuter"},
    {"id": 2200, "name": "Surgery"},
    {"id": 2300, "name": "Ultrasound"},
    {"id": 2400, "name": "Vaccinations"},
    {"id": 2500, "name": "Walk-Ins"},
    {"id": 2600, "name": "Wellness Exams"}
  ]
     ';

        return $this->responseService->success(['services' => json_decode($json, true)]);
    }
}
