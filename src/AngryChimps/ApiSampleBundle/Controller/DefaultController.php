<?php

namespace AngryChimps\ApiSampleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsApiSampleBundle:Default:index.html.twig', array('name' => $name));
    }
}
