<?php

namespace AngryChimps\AcBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        echo 'blah';
        return $this->render('AngryChimpsAcBundle:Default:index.html.twig', array());
    }
}
