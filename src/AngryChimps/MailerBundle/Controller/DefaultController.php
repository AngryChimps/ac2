<?php

namespace AngryChimps\MailerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AngryChimpsMailerBundle:Default:index.html.twig', array('name' => $name));
    }
}
