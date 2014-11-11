<?php


namespace AngryChimps\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends FOSRestController {

    protected function getContent(Request $request) {
        return json_decode($request->getContent(), true);
    }

    protected function view($data = null, $statusCode = null,
                            array $headers = array(), array $errors = array(),
                            array $debug = array())
    {
        $return = array(
            'data' => $data,
            'errors' => $errors,
            'debug' => $debug,
        );
        
        $view = parent::view($return, $statusCode, $headers);
        return $view->setFormat('json');
    }


} 