<?php

namespace AngryChimps\ApiSampleBundle\Controller;

use AngryChimps\ApiSampleBundle\services\DocsService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DocsController extends Controller
{
    public function indexAction() {
        /** @var DocsService $docs */
        $docs = $this->get('angry_chimps_api_sample.docs');

        $data = $docs->getTwigData();

        return $this->render('AngryChimpsApiSampleBundle:Docs:index.html.twig', $data);
    }
}
