<?php

namespace AngryChimps\ElasticsearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsElasticsearchBundle:Default:index.html.twig', array('name' => $name));
    }
}
