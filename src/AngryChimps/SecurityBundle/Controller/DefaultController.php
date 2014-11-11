<?php

namespace AngryChimps\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsSecurityBundle:Default:index.html.twig', array('name' => $name));
    }
}
