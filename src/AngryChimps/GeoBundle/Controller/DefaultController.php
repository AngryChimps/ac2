<?php

namespace AngryChimps\GeoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsGeoBundle:Default:index.html.twig', array('name' => $name));
    }
}
