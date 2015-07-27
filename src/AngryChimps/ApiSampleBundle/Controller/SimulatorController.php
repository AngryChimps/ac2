<?php

namespace AngryChimps\ApiSampleBundle\Controller;

use AngryChimps\ApiSampleBundle\services\DocsService;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SimulatorController extends Controller
{
    public function indexAction($api, $slug, Request $request) {
        /** @var DocsService $docs */
        $docs = $this->get('angry_chimps_api_sample.docs');

        /** @var ViewHandler $handler */
        $handler = $this->get('fos_rest.view_handler');

        $method = strtolower($request->getMethod());

        $response = $docs->getSimulatorResponse($api, $method, $slug);

        $view = $this->render('AngryChimpsApiSampleBundle:Simulator:index.html.twig', ['json' => $response]);
        $view->headers->set('Content-Type', 'application/json');
        return $view;
    }
}
