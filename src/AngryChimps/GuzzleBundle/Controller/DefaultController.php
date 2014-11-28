<?php

namespace AngryChimps\GuzzleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsGuzzleBundle:Default:index.html.twig', array('name' => $name));
    }
}
