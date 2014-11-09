<?php

namespace AngryChimps\AcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsAcBundle:Default:index.html.twig', array('name' => $name));
    }
}