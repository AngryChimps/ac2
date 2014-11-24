<?php

namespace AC\NormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ACNormBundle:Default:index.html.twig', array('name' => $name));
    }
}
