<?php

namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

class DefaultController extends FOSRestController
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsApiBundle:Default:index.html.twig', array('name' => $name));
    }
}
